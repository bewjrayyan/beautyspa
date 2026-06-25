<?php

namespace Modules\Order\Http\Controllers\Admin;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Modules\Order\Entities\Order;
use Modules\Admin\Traits\HasCrudActions;
use Modules\GoogleIntegration\Support\GoogleSheetsColumnConfig;
use Modules\Order\Events\OrderUpdated;
use Modules\Order\Http\Requests\SaveOrderRequest;

class OrderController
{
    use HasCrudActions {
        show as protected crudShow;
        update as protected crudUpdate;
        destroy as protected crudDestroy;
    }

    /**
     * Model for the resource.
     *
     * @var string
     */
    protected $model = Order::class;

    /**
     * The relations to eager load on every query.
     *
     * @var array
     */
    protected $with = ['products', 'coupon', 'taxes', 'beautician', 'spaBranch', 'transaction', 'customer'];

    /**
     * Label of the resource.
     *
     * @var string
     */
    protected $label = 'order::orders.order';

    /**
     * View path of the resource.
     *
     * @var string
     */
    protected $viewPath = 'order::admin.orders';

    /**
     * @var array|string
     */
    protected $validation = SaveOrderRequest::class;

    public function index(Request $request)
    {
        if ($request->has('query')) {
            return $this->getModel()
                ->search($request->get('query'))
                ->query()
                ->limit($request->get('limit', 10))
                ->get();
        }

        return view("{$this->viewPath}.index", [
            'archivedCount' => Order::onlyTrashed()->count(),
        ]);
    }

    /**
     * @param int $id
     *
     * @return \Illuminate\Contracts\View\View|\Illuminate\Http\RedirectResponse
     */
    public function show($id)
    {
        try {
            return $this->crudShow($id);
        } catch (ModelNotFoundException) {
            return redirect()
                ->route('admin.orders.index')
                ->withError(trans('order::orders.not_found', ['id' => $id]));
        }
    }

    /**
     * @param int $id
     */
    public function update($id): Response
    {
        $entity = $this->getEntity($id);
        $request = $this->getRequest('update');

        $this->disableSearchSyncing();

        $entity->update($request->validated());

        if (collect(GoogleSheetsColumnConfig::syncRelevantOrderAttributes())->contains(
            fn (string $attribute) => $entity->wasChanged($attribute)
        )) {
            event(new OrderUpdated($entity->fresh()));
        }

        $entity->withoutEvents(function () use ($entity) {
            $entity->touch();
        });

        $this->searchable($entity);

        if (method_exists($this, 'redirectTo')) {
            return $this->redirectTo($entity)
                ->withSuccess(trans('admin::messages.resource_updated', ['resource' => $this->getLabel()]));
        }

        if (request()->wantsJson()) {
            return response()->json(
                [
                    'success' => true,
                    'message' => trans('admin::messages.resource_updated', ['resource' => $this->getLabel()]),
                ],
                200
            );
        }

        return redirect()->route("{$this->getRoutePrefix()}.index")
            ->withSuccess(trans('admin::messages.resource_updated', ['resource' => $this->getLabel()]));
    }

    public function destroy(string $ids): void
    {
        $this->crudDestroy($ids);
    }

    /**
     * Permanently remove archived (soft-deleted) orders from the database.
     */
    public function forceDestroy(string $ids): void
    {
        $orderIds = array_values(array_filter(array_map('intval', explode(',', $ids))));

        if ($orderIds === []) {
            throw new NotFoundHttpException();
        }

        $orders = Order::onlyTrashed()->whereIn('id', $orderIds)->get();

        if ($orders->count() !== count($orderIds)) {
            throw new NotFoundHttpException();
        }

        foreach ($orders as $order) {
            $order->forceDelete();
        }
    }

    protected function getEntity(int|string $id): Order
    {
        return $this->getModel()
            ->with($this->relations())
            ->withoutGlobalScope('active')
            ->withTrashed()
            ->findOrFail($id);
    }
}

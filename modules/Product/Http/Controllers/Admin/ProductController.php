<?php

namespace Modules\Product\Http\Controllers\Admin;

use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Contracts\View\View;
use Modules\Product\Entities\Product;
use Illuminate\Contracts\View\Factory;
use Illuminate\Foundation\Application;
use Modules\Admin\Traits\HasCrudActions;
use Modules\Product\Http\Requests\SaveProductRequest;
use Modules\Product\Services\ProductCloneService;
use Modules\Product\Transformers\ProductEditResource;

class ProductController
{
    use HasCrudActions;

    /**
     * Model for the resource.
     *
     * @var string
     */
    protected string $model = Product::class;

    /**
     * Label of the resource.
     *
     * @var string
     */
    protected string $label = 'product::products.product';

    /**
     * View path of the resource.
     *
     * @var string
     */
    protected string $viewPath = 'product::admin.products';

    /**
     * Form requests for the resource.
     *
     * @var array|string
     */
    protected string|array $validation = SaveProductRequest::class;


    /**
     * Store a newly created resource in storage.
     *
     * @return Response|JsonResponse
     */
    public function store()
    {
        $this->disableSearchSyncing();

        $entity = $this->getModel()->create(
            $this->getRequest('store')->all()
        );

        $this->searchable($entity);

        $message = trans('admin::messages.resource_created', ['resource' => $this->getLabel()]);

        if (request()->query('exit_flash')) {
            session()->flash('exit_flash', $message);
        }

        if (request()->wantsJson()) {
            return response()->json(
                [
                    'success' => true,
                    'message' => $message,
                ],
                200
            );
        }

        return redirect()->route("{$this->getRoutePrefix()}.index")
            ->withSuccess($message);
    }


    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     *
     * @return Factory|View|Application
     */
    public function edit($id): Factory|View|Application
    {
        $entity = $this->getEntity($id);
        $productEditResource = new ProductEditResource($entity);

        return view(
            "{$this->viewPath}.edit",
            [
                'product' => $entity,
                'product_resource' => $productEditResource->response()->content(),
            ]
        );
    }


    /**
     * Update the specified resource in storage.
     *
     * @param int $id
     */
    public function updateStatus($id)
    {
        $entity = $this->getEntity($id);

        $isActive = filter_var(request('is_active'), FILTER_VALIDATE_BOOLEAN);

        $this->disableSearchSyncing();

        Product::withoutEvents(function () use ($entity, $isActive) {
            $entity->update(['is_active' => $isActive]);
        });

        $entity->refresh();

        $this->searchable($entity);

        $message = $isActive
            ? trans('product::products.status_enabled')
            : trans('product::products.status_disabled');

        return response()->json([
            'success' => true,
            'message' => $message,
            'is_active' => $entity->is_active,
        ]);
    }


    public function clone($id, ProductCloneService $productCloneService)
    {
        $entity = $this->getEntity($id);

        $this->disableSearchSyncing();

        $clone = $productCloneService->clone($entity);

        $this->searchable($clone);

        $message = trans('product::products.clone_success');

        if (request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'redirect' => route('admin.products.edit', $clone),
            ]);
        }

        return redirect()->route('admin.products.edit', $clone)
            ->withSuccess($message);
    }


    public function update($id)
    {
        $entity = $this->getEntity($id);

        $this->disableSearchSyncing();

        $entity->update(
            $this->getRequest('update')->all()
        );

        $entity->withoutEvents(function () use ($entity) {
            $entity->touch();
        });

        $productEditResource = new ProductEditResource($entity);

        $this->searchable($entity);

        $message = trans('admin::messages.resource_updated', ['resource' => $this->getLabel()]);

        if (request()->query('exit_flash')) {
            session()->flash('exit_flash', $message);
        }

        if (request()->wantsJson()) {
            return response()->json(
                [
                    'success' => true,
                    'message' => $message,
                    'product_resource' => $productEditResource,
                ],
                200
            );
        }
    }
}

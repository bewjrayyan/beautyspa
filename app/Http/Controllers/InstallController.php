<?php

namespace AestheticCart\Http\Controllers;

use Exception;
use AestheticCart\Install\App;
use AestheticCart\Install\Store;
use Illuminate\Http\Response;
use AestheticCart\Install\Database;
use AestheticCart\Install\Permission;
use AestheticCart\Install\PostInstall;
use Illuminate\Http\JsonResponse;
use AestheticCart\Install\Requirement;
use Illuminate\Routing\Controller;
use Illuminate\Contracts\View\View;
use AestheticCart\Install\AdminAccount;
use AestheticCart\Install\HostingEnvironment;
use Illuminate\Contracts\View\Factory;
use Illuminate\Support\Facades\Artisan;
use AestheticCart\Install\EnvironmentBootstrap;
use AestheticCart\Http\Requests\InstallRequest;
use Jackiedo\DotenvEditor\Facades\DotenvEditor;
use Illuminate\Contracts\Foundation\Application;
use AestheticCart\Http\Middleware\RedirectIfInstalled;
use Modules\Setting\Entities\Setting;
use Modules\Setting\Repositories\SettingRepository;

class InstallController extends Controller
{
    public function __construct()
    {
        $this->middleware(RedirectIfInstalled::class);
    }


    public function installation(
        Requirement $requirement,
        Permission $permission,
        EnvironmentBootstrap $bootstrap,
        HostingEnvironment $hosting
    ): Factory|View|Application {
        $bootstrap->ensureEnvFileExists();
        $permission->prepare();

        return view('install.install', [
            'requirement' => $requirement,
            'permission' => $permission,
            'hosting' => $hosting->profile(),
            'uploadChecks' => $hosting->uploadChecks(),
            'suggestedAppUrl' => $hosting->suggestedAppUrl(),
        ]);
    }


    public function install(
        InstallRequest $request,
        Database $database,
        AdminAccount $admin,
        Store $store,
        App $app,
        PostInstall $postInstall
    ): JsonResponse {
        @set_time_limit(0);

        try {
            Artisan::call('optimize:clear');

            $database->setup($request);
            $admin->setup($request);
            $store->setup($request);
            $app->setup($request);

            DotenvEditor::setKey('APP_INSTALLED', 'true')->save();

            config(['app.installed' => true]);
            $this->registerSettingRepository();

            Artisan::call('key:generate', ['--force' => true]);

            $postInstall->run();

            $success = true;
            $message = trans('install.messages.success');
        } catch (Exception $e) {
            $success = false;
            $message = $e->getMessage();

            try {
                Artisan::call('db:wipe', ['--force' => true]);
            } catch (Exception $rollbackException) {
                $message .= '<br><br>'.$rollbackException->getMessage();
            }
        } finally {
            return response()->json(
                [
                    'success' => $success,
                    'message' => $message,
                ],
                $success ? Response::HTTP_OK : Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }


    private function registerSettingRepository(): void
    {
        if (app()->bound('setting')) {
            return;
        }

        app()->singleton('setting', function () {
            return new SettingRepository(Setting::allCached());
        });
    }
}

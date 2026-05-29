<?php

namespace Modules\Core\Http\Middleware;

use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class FixSubdirectoryLocalizedRedirect
{
    /**
     * Ensure locale redirects include the APP_URL subdirectory (e.g. /fleetcart).
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if ($response instanceof RedirectResponse) {
            $location = $response->headers->get('Location');

            if (is_string($location) && $location !== '') {
                $response->headers->set('Location', aestheticcart_normalize_install_url(
                    aestheticcart_apply_install_base_url($location)
                ));
            }
        }

        return $response;
    }
}

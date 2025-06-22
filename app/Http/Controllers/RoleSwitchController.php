<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class RoleSwitchController extends Controller
{
    /**
     * Valid roles that can be switched to
     */
    protected const VALID_ROLES = ['admin', 'tutor', 'student'];

    /**
     * Session keys for role switching
     */
    protected const SESSION_ORIGINAL_ROLE = 'original_role';

    protected const SESSION_CURRENT_ROLE = 'current_role';

    /**
     * Switch user to a different role
     */
    public function switchToRole(Request $request, string $role): RedirectResponse
    {
        try {
            $user = Auth::user();

            // Security check: Only admins can switch roles
            $this->validateAdminAccess($user, $role);

            // Validate the requested role
            $this->validateRole($role);

            // Handle switching back to original role
            if ($role === $user->role) {
                return $this->clearRoleSwitch($request);
            }

            // Store role switching state
            $this->setRoleSwitchSession($request, $user->role, $role);

            $redirectRoute = $this->getRedirectRoute($role);
            $message = __('common.switched_to_role', ['role' => ucfirst($role)]);

            Log::info("Admin user {$user->id} switched to role: {$role}");

            return redirect()->route($redirectRoute)->with('success', $message);

        } catch (Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Switch back to original role
     */
    public function switchBack(Request $request): RedirectResponse
    {
        try {
            $user = Auth::user();

            // Validate switch back conditions
            $this->validateSwitchBack($request, $user);

            $originalRole = $request->session()->get(self::SESSION_ORIGINAL_ROLE, $user->role);
            $currentRole = $request->session()->get(self::SESSION_CURRENT_ROLE);

            // Clear role switching session data
            $this->clearRoleSwitchSession($request);

            $redirectRoute = $this->getRedirectRoute($originalRole);

            Log::info("User {$user->id} switched back from role '{$currentRole}' to original role: {$originalRole}");

            return redirect()->route($redirectRoute)
                ->with('success', __('common.returned_to_original_role'));

        } catch (Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Get current role status for user
     */
    public function getCurrentRole(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $user = Auth::user();
            $originalRole = $request->session()->get(self::SESSION_ORIGINAL_ROLE);
            $currentRole = $request->session()->get(self::SESSION_CURRENT_ROLE);

            return response()->json([
                'user_role' => $user->role,
                'original_role' => $originalRole,
                'current_role' => $currentRole,
                'is_switching' => $request->session()->has(self::SESSION_CURRENT_ROLE),
            ]);

        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * Validate admin access for role switching
     */
    protected function validateAdminAccess($user, string $requestedRole): void
    {
        if ($user->role !== 'admin') {
            Log::warning("Non-admin user {$user->id} attempted to switch roles", [
                'user_id' => $user->id,
                'user_role' => $user->role,
                'requested_role' => $requestedRole,
            ]);
            throw new Exception(__('common.access_denied'));
        }
    }

    /**
     * Validate requested role
     */
    protected function validateRole(string $role): void
    {
        if (! in_array($role, self::VALID_ROLES)) {
            Log::warning('Invalid role switch attempt', [
                'requested_role' => $role,
                'valid_roles' => self::VALID_ROLES,
            ]);
            throw new Exception(__('common.invalid_role'));
        }
    }

    /**
     * Validate switch back conditions
     */
    protected function validateSwitchBack(Request $request, $user): void
    {
        if (! $request->session()->has(self::SESSION_CURRENT_ROLE)) {
            throw new Exception(__('common.not_switching_roles'));
        }

        if ($user->role !== 'admin') {
            Log::warning("Non-admin user {$user->id} attempted to switch back roles", [
                'user_id' => $user->id,
                'user_role' => $user->role,
            ]);
            throw new Exception(__('common.access_denied'));
        }
    }

    /**
     * Set role switching session data
     */
    protected function setRoleSwitchSession(Request $request, string $originalRole, string $newRole): void
    {
        if (! $request->session()->has(self::SESSION_ORIGINAL_ROLE)) {
            $request->session()->put(self::SESSION_ORIGINAL_ROLE, $originalRole);
        }
        $request->session()->put(self::SESSION_CURRENT_ROLE, $newRole);
    }

    /**
     * Clear role switching session data
     */
    protected function clearRoleSwitchSession(Request $request): void
    {
        $request->session()->forget([self::SESSION_ORIGINAL_ROLE, self::SESSION_CURRENT_ROLE]);
    }

    /**
     * Clear role switch and redirect to admin dashboard
     */
    protected function clearRoleSwitch(Request $request): RedirectResponse
    {
        $this->clearRoleSwitchSession($request);

        Log::info('Admin user returned to original role');

        return redirect()->route('admin.dashboard')
            ->with('success', __('common.viewing_normal_role'));
    }

    /**
     * Get redirect route based on role
     */
    protected function getRedirectRoute(string $role): string
    {
        return match ($role) {
            'admin' => 'admin.dashboard',
            'tutor' => 'dashboard',
            'student' => 'dashboard',
            default => 'dashboard'
        };
    }
}

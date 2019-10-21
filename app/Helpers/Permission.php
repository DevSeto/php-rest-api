<?php

namespace App\Helpers;

use App\Models\Permissions;
use DB;
use Response;
use Lang;
use Route;


class Permission
{

    /**
     * Get permission from Db by permissions ID
     *
     * @param integer $permissionId
     *
     * @return array
     */
    public static function getPermission($permissionId)
    {
        return (!empty(Permissions::find($permissionId))) ? Permissions::find($permissionId)->toArray() : [];
    }

    /**
     * Check User Permissions
     *
     * @param array $user
     * @param string $requestUrl
     * @param string $requestType
     *
     * @return bool
     */
    public static function checkUserPermissions($user, $requestUrl, $requestType)
    {

        $companyDefaultPermissionsClass = new CompanyDefaultPermissions();
        $companyDefaultPermissions = get_object_vars($companyDefaultPermissionsClass);

        $permissionId = false;
        $defaultPermissionsIDs = json_decode($user['user_roles']['default_permissions_ids'], true);
        $permissionsIDs = json_decode($user['user_roles']['permissions_ids'], true);
        $userDefaultPermissionsIDs = array_merge($defaultPermissionsIDs, $permissionsIDs);

        foreach ($companyDefaultPermissions as $companyDefaultPermission) {
            $key = array_search($requestUrl, $companyDefaultPermission);
            ($key) ?: $permissionId = $companyDefaultPermission['id'];
        }

        $permission = array_search($permissionId, $userDefaultPermissionsIDs);
        return ($permission) ? true : false;
    }
}

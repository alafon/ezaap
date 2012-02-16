<?php

/**
 * Login handler used to connect a user
 */
class ezsfConnectUser extends eZUser
{
    public static function loginUser( $login, $password, $authenticationMatch = false )
    {
        $debugLabel = "Authentication using ezsfServiceAccountHandler";
        /* @var ezsfServiceAccountHandler $authService */
        $authService = ezsfService::get( ezsfServiceAccountHandler::SERVICE_NAME );
        $authService->Authenticate( array( '_username' => $login,
                                           '_password' => $password ));

        if( $authService->isLoggedIn() )
        {
            eZDebug::writeDebug( "User $login logged in. Token: " . $authService->getToken(), $debugLabel );
            $sfUser = self::createWithSFData( $authService->getUserData() );
            return $sfUser;
        }
        else
        {
            eZDebug::writeDebug("User $login not logged", $debugLabel );
            return false;
        }
    }

    /**
     *
     * Create a new ezsfConnectUser by trying to fetch the matching eZ Publish
     * user using the roles returned by the backend
     *
     * @todo à améliorer avec le formalisme qui sera choisit
     * @param ezsfUser $role
     * @return ezsfConnectUser
     */
    public static function createWithSFData( $sfData )
    {
        $user = self::fetchByRoles( $sfData->roles );
        self::extendSessionData( $sfData );
        return $user;
    }

    /**
     *
     * @param type $roles
     * @return type
     */
    private static function fetchByRoles( $roles )
    {
        $roleString = implode( "-", $roles );
        return self::fetchByRole( $roleString );
    }

    /**
     *
     * @param type $role
     * @return ezsfConnectUser
     */
    private static function fetchByRole( $role )
    {
        // actuellement mapping 1-1 entre un role métier et le login d'un user
        return self::fetchByName( $role );
    }

    private static function extendSessionData( $sfData )
    {
        eZSession::set( ezsfUser::SESSION_VARNAME, new ezsfUser( $sfData ) );
    }
}

?>

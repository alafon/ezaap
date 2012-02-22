<?php

/**
 * Login handler used to connect a user
 *
 * Note : defined roles in the middleoffice
 *
   const ROLE_ADMIN                = 0;
   const ROLE_PURCHASER            = 1;
   const ROLE_LEGAL_REPRESENTATIVE = 2;
   const ROLE_ACCOUNTANT           = 3;
   const ROLE_CREATOR              = 4;
   const ROLE_TCA                  = 5;
 *
 */
class ezaapConnectUser extends eZUser
{
    public static function loginUser( $login, $password, $authenticationMatch = false )
    {
        $debugLabel = "Authentication using ezaapServiceAccountHandler";
        /* @var ezaapServiceAccountHandler $authService */
        $authService = ezaapService::get( ezaapServiceAccountHandler::SERVICE_NAME );
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
     * Create a new ezaapConnectUser by trying to fetch the matching eZ Publish
     * user using the roles returned by the backend
     *
     * @todo à améliorer avec le formalisme qui sera choisit
     * @param ezaapUser $role
     * @return ezaapConnectUser
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
     * @return ezaapConnectUser
     */
    private static function fetchByRole( $role )
    {
        // actuellement mapping 1-1 entre un role métier et le login d'un user
        return self::fetchByName( $role );
    }

    private static function extendSessionData( $sfData )
    {
        eZSession::set( ezaapUser::SESSION_VARNAME, new ezaapUser( $sfData ) );
    }
}

?>

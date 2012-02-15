<?php

/**
 * Structure contenant un objet inspectable depuis les templates
 */
class ezsfUser
{
    const SESSION_VARNAME = 'ezsfUserData';

    public $id;
    public $username;
    public $token;
    public $roles;
    public $is_b2b;
    public $business;
    public $country;

    function __construct( $ezsfData = false )
    {
        if( $ezsfData !== false )
        {
            foreach( get_object_vars( $ezsfData ) as $attribute => $value )
            {
                $this->$attribute = $value;
            }
        }
    }

    /**
     *
     * @return ezsfUser
     */
    static function getFromSessionObject()
    {
        // hack
        // @todo detruire la session correctement via un handler
        if( !eZUser::currentUser()->isLoggedIn() )
        {
            eZSession::unsetkey( self::SESSION_VARNAME );
            eZSession::set( self::SESSION_VARNAME, new self() );
        }

        $sessionObject = eZSession::get( self::SESSION_VARNAME );
        return $sessionObject;
    }

    function attributes()
    {
        return array_keys( get_object_vars( $this ) );
    }

    function hasAttribute( $key )
    {
        return array_search( $key, $this->attributes() );
    }

    function attribute( $key )
    {
        return $this->$key;
    }
}

?>

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

    public $cookies = array();

    function __construct( $ezsfData = false )
    {
        if( $ezsfData !== false )
        {
            foreach( get_object_vars( $ezsfData ) as $attribute => $value )
            {
                $this->$attribute = $value;
            }
        }
        $this->setCookie( '_token', $this->token );
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

    /**
     *
     * @return ezsfUser
     */
    static public function instance()
    {
        return self::getFromSessionObject();
    }

    public function setCookie( $key, $value )
    {
        $this->cookies[$key] = $value;
    }

    public function hasCookie( $key )
    {
        return array_key_exists( $key, $this->cookies );
    }

    public function getCookie( $key )
    {
        return $this->hasCookie( $key ) ? $this->cookies[$key] : null;
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

    function store()
    {
        eZSession::set( self::SESSION_VARNAME , $this );
    }

    /**
     *
     * Returns the business id if selected, otherwise returns false
     *
     * @return type
     */
    public function selectedBusiness()
    {
        return ($this->business != 0 ? $this->business : false);
    }
}

?>

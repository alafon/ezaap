<?php

class ezaapUserType extends eZDataType
{
    const DATA_TYPE_STRING = "ezaapuser";

    function __construct()
    {
        $this->eZDataType( self::DATA_TYPE_STRING, ezpI18n::tr( 'kernel/classes/datatypes', "ezaapUser", 'Datatype name' ),
                           array( 'serialize_supported' => true ) );
    }

    function objectAttributeContent( $contentObjectAttribute )
    {
        // retourne un object inspectable dans les templates
        return ezaapUser::getFromSessionObject();
    }
}

eZDataType::register( ezaapUserType::DATA_TYPE_STRING, "ezaapusertype" );

?>

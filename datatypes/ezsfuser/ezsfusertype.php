<?php

class ezsfUserType extends eZDataType
{
    const DATA_TYPE_STRING = "ezsfuser";

    function __construct()
    {
        $this->eZDataType( self::DATA_TYPE_STRING, ezpI18n::tr( 'kernel/classes/datatypes', "ezsfUser", 'Datatype name' ),
                           array( 'serialize_supported' => true ) );
    }

    function objectAttributeContent( $contentObjectAttribute )
    {
        // retourne un object inspectable dans les templates
        return ezsfUser::getFromSessionObject();
    }
}

eZDataType::register( ezsfUserType::DATA_TYPE_STRING, "ezsfusertype" );

?>

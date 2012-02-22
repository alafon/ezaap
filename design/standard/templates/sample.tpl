{*
   Examples d'utilisation des fonctionnalités template de ezaap

   Retourne les données users renvoyées par le backoffice metier apres
   l'authentification
   {$current_user.contentobject.data_map.ezaapuserdata.content}

   Appel d'un service
   <servicename> : le nom du service
   <servicemethod> : le nom de la method
   <usecurrent_token> : false|true selon si on veut utiliser le token actuel
   <arguments> : des arguments qui seront utilisés par la method (disponibles dans $ezaapService->requestArguments)
   {ezaapservice( '<servicename>', '<servicemethod>', <usecurrent_token>, <arguments> ) )}
*}

{if $current_user.is_logged_in}
Logged in with token = {$current_user.contentobject.data_map.ezaapuserdata.content.token}
<br />
{/if}


{ezaapservice( 'Account', 'Authenticate', true, hash( '_username', 'admin@french_language_school_1.com',
                                                     '_password', 'sensio' ) )}

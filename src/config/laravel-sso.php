<?php

return [

    /**
     * Brokers configurations for SSO Server
     */
    'brokers' => [
        'driver' => 'model', // Use model or array driver

        /**
         * Boker model class, required for model driver.
         */
        'model' => 'App\Models\App',

        /**
         * Boker model id field, required for model driver.
         */
        'id_field' => 'app_id',

        /**
         * Boker model secret field, required for model driver.
         */
        'secret_field' => 'secret',

        /**
         * Broker's list, required for list driver. ['id' => 'secret']
         */
        'list' => [],
    ],

    /**
     * Broker id for client configuration. Must be null on SSO Server
     */
    'broker_client_id' => null,

    /**
     * Broker secret for client configuration. Must be null on SSO Server
     */
    'broker_client_secret' => null,

    /**
     * Broker client unique username
     */
    'broker_client_username' => 'email',

    /**
     * The server Url. Required for clients.
     */
    'broker_server_url' => '',

    /**
     * The return Url. Required for clients.
     */
    'broker_return_url' => true,

    /**
     * Session live time Default to 60 minutes. Set to null to store forever
     */
    'session_ttl' => 60,

    /**
     * Closure that return the user info from server. This function allows you
     * to return additional payload data to the clients. By default, the user
     * attributes are returned by calling $user->toArray().
     * Eg. 'user_info' => function($user, $broker, $request) {
     *      $payload = $user->toArray();
     *      $payload['roles'] = $user->getRolesByApp($broker->id);
     *
     *      return $payload
     * }
     */
    'user_info' => null,

    /**
     * Enable debug mode
     */
    'debug' => false
];

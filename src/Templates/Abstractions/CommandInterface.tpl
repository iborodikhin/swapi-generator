<?php
namespace %api_namespace%\Abstractions;

/**
 * Command interface API.
 */
interface CommandInterface
{

    /**
     * Get request uri.
     *
     * @return string
     */
    public function getRequestUri();

    /**
     * Set authentication username.
     *
     * @param  string $username
     * @return void
     */
    public function setAuthUsername($username);

    /**
     * Get authentication username.
     *
     * @return string
     */
    public function getAuthUsername();

    /**
     * Set authentication password.
     *
     * @param  string $password
     * @return void
     */
    public function setAuthPassword($password);

    /**
     * Get authentication password.
     *
     * @return string
     */
    public function getAuthPassword();

    /**
     * Set query parameters.
     *
     * @param  array $query
     * @return void
     */
    public function setQuery(array $query);

    /**
     * Add query parameter.
     *
     * @param  string $name
     * @param  mixed  $value
     * @return void
     */
    public function addQuery($name, $value);

    /**
     * Get query parameters.
     *
     * @return array
     */
    public function getQuery();

    /**
     * Set POST parameters.
     *
     * @param  array $data
     * @return void
     */
    public function setPost(array $data);

    /**
     * Add POST parameter.
     *
     * @param  string $name
     * @param  mixed  $value
     * @return void
     */
    public function addPost($name, $value);

    /**
     * Get POST parameters.
     *
     * @return array
     */
    public function getPost();

    /**
     * Set files.
     *
     * @param  array $files
     * @return void
     */
    public function setFiles(array $files);

    /**
     * Get request method.
     *
     * @return string
     */
    public function getRequestMethod();

    /**
     * Get reuqest result. If set $execAndStop == true the response would be printed instead of return.
     *
     * @param  boolean $execAndStop
     * @return array
     * @throws \%api_namespace%\Exceptions\Command
     * @throws \%api_namespace%\Exceptions\HTTP
     * @throws \%api_namespace%\Exceptions\JSON
     */
    public function getResult($execAndStop = false);
}

<?php
namespace Baz\Abstractions;

use Buzz\Browser;
use Buzz\Client\Curl;
use Buzz\Listener\BasicAuthListener;
use Buzz\Message\Form\FormRequest;
use Buzz\Message\Form\FormUpload;
use Buzz\Message\RequestInterface;
use Baz\Api;
use Baz\Exceptions;

/**
 * Base command class.
 */
abstract class Command implements CommandInterface
{

    /**
     * API client.
     *
     * @var \Baz\Api
     */
    protected $api = null;

    /**
     * Query parameters.
     *
     * @var array
     */
    protected $query = [];

    /**
     * POST parameters.
     *
     * @var array
     */
    protected $post = [];

    /**
     * Files.
     *
     * @var array
     */
    protected $files = [];

    /**
     * Request method.
     *
     * @var string
     */
    protected $requestMethod = RequestInterface::METHOD_GET;

    /**
     * Authentication parameters.
     *
     * @var array
     */
    protected $auth = [
        'required' => false,
        'username' => '',
        'password' => '',
    ];

    /**
     * Constructor.
     *
     * @param \Baz\Api $api
     */
    public function __construct(Api $api)
    {
        $this->api = $api;
    }

    /**
     * {@inheritdoc}
     *
     * @param string $username
     */
    public function setAuthUsername($username)
    {
        $this->auth['username'] = $username;
        $this->auth['required'] = true;
    }

    /**
     * {@inheritdoc}
     *
     * @return string
     */
    public function getAuthUsername()
    {
        return $this->auth['username'];
    }

    /**
     * {@inheritdoc}
     *
     * @param string $password
     */
    public function setAuthPassword($password)
    {
        $this->auth['password'] = $password;
        $this->auth['required'] = true;
    }

    /**
     * {@inheritdoc}
     *
     * @return string
     */
    public function getAuthPassword()
    {
        return $this->auth['password'];
    }

    /**
     * {@inheritdoc}
     *
     * @param array $query
     */
    public function setQuery(array $query)
    {
        $this->query = $query;
    }

    /**
     * {@inheritdoc}
     *
     * @param string $name
     * @param mixed  $value
     */
    public function addQuery($name, $value)
    {
        $this->query[$name] = $value;
    }

    /**
     * {@inheritdoc}
     *
     * @return array
     */
    public function getQuery()
    {
        return $this->query;
    }

    /**
     * {@inheritdoc}
     *
     * @param array $data
     */
    public function setPost(array $data)
    {
        $this->post = $data;
    }

    /**
     * {@inheritdoc}
     *
     * @param string $name
     * @param mixed  $value
     */
    public function addPost($name, $value)
    {
        $this->post[$name] = $value;
    }

    /**
     * {@inheritdoc}
     *
     * @return array
     */
    public function getPost()
    {
        return $this->post;
    }

    /**
     * {@inheritdoc}
     *
     * @param  array $files
     * @throws \Baz\Exceptions\Command
     */
    public function setFiles(array $files)
    {
        foreach ($files as $file) {
            if (!is_array($file)) {
                throw new Exceptions\Command('Each entry of $files should be an array');
            }

            if (empty($file['name'])) {
                throw new Exceptions\Command('Each entry of $files should contain «name» option');
            }

            if (empty($file['type'])) {
                throw new Exceptions\Command('Each entry of $files should contain «type» option');
            }

            if (empty($file['file'])) {
                throw new Exceptions\Command('Each entry of $files should contain «file» option');
            } elseif (!is_readable($file['file'])) {
                throw new Exceptions\Command(
                    sprintf(
                        'Unable to read file «%s»',
                        $file['file']
                    )
                );
            }
        }

        $this->requestMethod = RequestInterface::METHOD_POST;
        $this->files = $files;
    }

    /**
     * {@inheritdoc}
     *
     * @return string
     */
    public function getRequestMethod()
    {
        return $this->requestMethod;
    }

    /**
     * {@inheritdoc}
     *
     * @param  boolean      $execAndStop
     * @return array|string
     * @throws \Baz\Exceptions\JSON
     * @throws \Baz\Exceptions\Command
     */
    public function getResult($execAndStop = false)
    {
        if ($this->auth['required'] && (empty($this->auth['username']) || empty($this->auth['password']))) {
            throw new Exceptions\Command('You must provide HTTP-authorization parameters for this method');
        }

        // Генерируем строку запроса
        $uri = sprintf(
            '%s?%s',
            $this->getRequestUri(),
            http_build_query(
                array_merge(
                    $this->getQuery(),
                    [
                        'appId'   => $this->api->getAppId(),
                        'appKey'  => $this->api->getAppKey(),
                    ]
                )
            )
        );

        // Создаем объект запроса
        $client = new Curl();
        $client->setTimeout($this->api->getTimeout());

        $browser = new Browser($client);
        $request = new FormRequest($this->requestMethod, $this->api->getBaseUrl() . $uri);

        if (null !== $this->api->getUserAgent()) {
            $request->addHeader(sprintf('User-Agent: %s', $this->api->getUserAgent()));
        }

        if ($this->requestMethod == RequestInterface::METHOD_POST) {
            if (!empty($this->post)) {
                foreach ($this->post as $k => $v) {
                    $request->setField($k, $v);
                }
            }
        }

        // Если нужно, добавляем параметры авторизации
        if ($this->auth['required']) {
            $browser->addListener(
                new BasicAuthListener($this->auth['username'], $this->auth['password'])
            );
        }

        // Добавляем файлы
        if (!empty($this->files)) {
            foreach ($this->files as $file) {
                $upload = new FormUpload($file['file'], $file['type']);
                $upload->setFilename($file['name']);
                $request->setField('upload', $upload);
            }
        }

        $execTime = microtime(true);
        $result   = $browser->send($request);

        $responseInfo = [
            'total_time' => microtime(true) - $execTime,
            'http_code'  => $result->getStatusCode(),
        ];

        $httpExc = null;
        if ($result->isClientError() || $result->isServerError()) {
            switch ($result->getStatusCode()) {
                case 404:
                    $httpExc = new Exceptions\HTTP(
                        sprintf(
                            'API returned error 404 on request to «%s»: %s',
                            $request->getUrl(),
                            $result->getContent()
                        ),
                        404
                    );
                    break;
                case 500:
                default:
                    $httpExc = new Exceptions\HTTP(
                        sprintf(
                            'API returned internal error on request to «%s»: %s',
                            $request->getUrl(),
                            $result->getContent()
                        ),
                        500
                    );
                    break;
            }
        }

        // Логируем запрос
        if ($this->api->getLogQueries()) {
            $this->api->logResponse($request->getUrl(), $this->requestMethod, $responseInfo);
        }

        if ($execAndStop) {
            echo $result->getContent();
            exit;
        }

        if (null !== $httpExc) {
            throw $httpExc;
        }

        $json    = json_decode($result->getContent(), true);
        $jsonErr = json_last_error();

        if (JSON_ERROR_NONE !== $jsonErr) {
            throw new Exceptions\JSON(
                sprintf(
                    '%s on %s',
                    $this->getJsonErrorText($jsonErr),
                    $request->getUrl()
                )
            );
        }

        return $json;
    }

    /**
     * Get text representation of JSON error.
     *
     * @param  integer $jsonErr
     * @return string
     */
    protected function getJsonErrorText($jsonErr)
    {
        switch ($jsonErr) {
            case JSON_ERROR_DEPTH:
                $msg = 'The maximum stack depth has been exceeded';
                break;
            case JSON_ERROR_STATE_MISMATCH:
                $msg = 'Invalid or malformed JSON';
                break;
            case JSON_ERROR_CTRL_CHAR:
                $msg = 'Control character error, possibly incorrectly encoded';
                break;
            case JSON_ERROR_SYNTAX:
                $msg = 'Syntax error';
                break;
            case JSON_ERROR_UTF8:
                $msg = 'Malformed UTF-8 characters, possibly incorrectly encoded';
                break;
            default:
                $msg = 'Unknown error occured while parsing JSON from API';
                break;
        }

        return $msg;
    }
}

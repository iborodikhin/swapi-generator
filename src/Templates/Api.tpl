<?php
namespace %api_namespace%;

/**
 * API client.
 */
class Api
{

    /**
     * Base API url.
     *
     * @var string
     */
    protected $baseUrl = null;

    /**
     * Operation timeout.
     *
     * @var integer
     */
    protected $timeout = 30;

    /**
     * User-Agent
     *
     * @var null|string
     */
    protected $userAgent = null;

    /**
     * Application ID.
     *
     * @var string
     */
    protected $appId = null;

    /**
     * Application key.
     *
     * @var string
     */
    protected $appKey = null;

    /**
     * Use API requests log.
     *
     * @var boolean
     */
    protected $logQueries = false;

    /**
     * API requests log.
     *
     * @var array
     */
    protected $responseLog = [];

    /**
     * Registered commands.
     *
     * @var array
     */
    private $commands = %api_commands%;

    /**
     * Local cache for commands.
     *
     * @var \ReflectionClass[]
     */
    private $commandsCache = [];

    /**
     * Constructor.
     *
     * @param  array $options
     * @throws \%api_namespace%\Exceptions\Configuration
     */
    public function __construct(array $options)
    {
        if (empty($options['domain'])) {
            throw new Exceptions\Configuration("«domain» option is required");
        }

        if (empty($options['appId'])) {
            throw new Exceptions\Configuration("«appId» option is required");
        } elseif (!is_scalar($options['appId']) || !preg_match('#^\d{12}$#', $options['appId'])) {
            throw new Exceptions\Configuration('Invalid «appId» option provided');
        }

        if (empty($options['appKey'])) {
            throw new Exceptions\Configuration("«appKey» option is required");
        } elseif (!is_scalar($options['appKey']) || !preg_match('#^[0-9a-f]{32}$#i', $options['appKey'])) {
            throw new Exceptions\Configuration('Invalid «appKey» option provided');
        }

        $this->appId   = $options['appId'];
        $this->appKey  = strtolower($options['appKey']);

        $scheme = 'http';
        $port   = 80;
        if (!empty($options['useSsl']) && $options['useSsl']) {
            $scheme = 'https';
            $port   = 443;
        }

        if (!empty($options['port'])) {
            $port = $options['port'];
        }

        $this->baseUrl = sprintf(
            '%s://%s:%s/',
            $scheme,
            $options['domain'],
            $port
        );

        if (!empty($options['timeout'])) {
            $this->timeout = (int) $options['timeout'];
        }
    }

    /**
     * Returns base API url.
     *
     * @return string
     */
    public function getBaseUrl()
    {
        return $this->baseUrl;
    }

    /**
     * Returns User-Agent.
     *
     * @return null|string
     */
    public function getUserAgent()
    {
        return $this->userAgent;
    }

    /**
     * Set User-Agent.
     *
     * @param  string $ua
     * @return void
     */
    public function setUserAgent($ua)
    {
        $this->userAgent = $ua;
    }

    /**
     * Returns application ID.
     *
     * @return string
     */
    public function getAppId()
    {
        return $this->appId;
    }

    /**
     * Returns application key.
     *
     * @return string
     */
    public function getAppKey()
    {
        return $this->appKey;
    }

    /**
     * Returns API requests log.
     *
     * @return array
     */
    public function getResponseLog()
    {
        return $this->responseLog;
    }

    /**
     * Returns timeout.
     *
     * @return integer
     */
    public function getTimeout()
    {
        return $this->timeout;
    }

    /**
     * Generates API command.
     *
     * @param  string $method
     * @param  array  $arguments
     * @return \%api_namespace%\Abstractions\Command
     * @throws \%api_namespace%\Exceptions\Exception
     */
    public function __call($method, $arguments)
    {
        $lower = strtolower($method);

        if (!isset($this->commands[$lower])) {
            throw new Exceptions\Command(sprintf(
                '«%s» is not a registered API command',
                $method
            ));
        }

        array_unshift($arguments, $this);

        if (!array_key_exists($lower, $this->commandsCache)) {
            $this->commandsCache[$lower] = new \ReflectionClass('\%api_namespace%\Commands\\' . $this->commands[$lower]);
        }

        return $this->commandsCache[$lower]->newInstanceArgs($arguments);
    }

    /**
     * Turn logging on.
     *
     * @return void
     */
    public function initLog()
    {
        // Логирование включаем только один раз
        if (!$this->logQueries) {
            $this->logQueries = true;
        }
    }

    /**
     * Do we need to log queries.
     *
     * @return boolean
     */
    public function getLogQueries()
    {
        return $this->logQueries;
    }

    /**
     * Save request to log.
     *
     * @param  string $uri
     * @param  string $method
     * @param  array  $responseInfo
     * @return void
     */
    public function logResponse($uri, $method, array $responseInfo)
    {
        $exclude = ['appId', 'appKey', 'password'];
        $query   = [];
        $parts   = parse_url($uri);
        parse_str($parts['query'], $query);
        foreach ($query as $k => $v) {
            if (in_array($k, $exclude)) {
                unset($query[$k]);
            }
        }

        // Обрезаем строку запроса
        $query = urldecode(http_build_query($query));
        if (strlen($query) > 60) {
            $pos = strpos($query, '&', 60);
            if (false !== $pos) {
                $query = substr($query, 0, $pos) . '…';
            }
        }

        if (!empty($query)) {
            $url = $parts['path'] . '?' . ltrim($query, '?');
        } else {
            $url = $parts['path'];
        }

        $this->responseLog[] = [
            'url'    => $url,
            'code'   => $responseInfo['http_code'],
            'time'   => $responseInfo['total_time'],
            'method' => $method,
        ];
    }
}

<?php

namespace Zver {


    class CurrentURL
    {

        protected $REQUIRED_DATA = [
            'HOST'  => null,
            'HTTPS' => null,
            'GET'   => null,
            'PORT'  => null,
            'PATH'  => null,
        ];

        public function getPath()
        {
            return $this->REQUIRED_DATA['PATH'];
        }

        public function getPort()
        {
            return $this->REQUIRED_DATA['PORT'];
        }

        public function getHost()
        {
            return $this->REQUIRED_DATA['HOST'];
        }


        protected static function isStandardPort($port)
        {
            return in_array($port, [
                80,
                443
            ]);
        }

        public function get()
        {
            $url = StringHelper::load('http');

            if ($this->REQUIRED_DATA['HTTPS']) {
                $url->append('s');
            }

            $url->append('://')
                ->append($this->REQUIRED_DATA['HOST']);

            if (!static::isStandardPort($this->REQUIRED_DATA['PORT'])) {
                $url->append(':')
                    ->append($this->REQUIRED_DATA['PORT']);
            }

            $url->append($this->REQUIRED_DATA['PATH']);

            if (!empty($this->REQUIRED_DATA['GET'])) {
                $url->append('?')
                    ->append(http_build_query($this->REQUIRED_DATA['GET']));
            }

            return $url->get();
        }

        protected function isLoaded()
        {
            foreach ($this->REQUIRED_DATA as $key => $value) {
                if (is_null($value)) {
                    return false;
                }
            }
            return true;
        }

        protected function getHostFromEnv()
        {
            if (!empty($_SERVER['HTTP_HOST'])) {
                return StringHelper::load($_SERVER['HTTP_HOST'])
                                   ->trimSpaces()
                                   ->toLowerCase()
                                   ->setFirstPart(':')
                                   ->removeEnding('/')
                                   ->get();
            }

            return null;
        }

        protected function getPortFromEnv()
        {
            if (!empty($_SERVER['SERVER_PORT'])) {
                return $_SERVER['SERVER_PORT'];
            }

            return null;
        }

        protected function getQueryFromEnv()
        {
            if (!empty($_GET)) {
                $filtered = [];
                $keys = array_keys($_GET);
                foreach ($keys as $key) {
                    $filtered[$key] = filter_input(INPUT_GET, $key);
                }
                return $filtered;
            }
            return [];
        }

        protected function getPathFromEnv()
        {
            if (isset($_SERVER['REQUEST_URI'])) {

                $path = StringHelper::load($_SERVER['REQUEST_URI'])
                                    ->setFirstPart('?')
                                    ->ensureBeginningIs('/');

                if (!$path->isEquals('/')) {
                    $path->removeEnding('/');
                }

                return $path->get();
            }

            return null;
        }

        protected function isSecureFromEnv()
        {
            $secure = false;

            if (!empty($_SERVER)) {

                if (

                    !empty($_SERVER['REQUEST_SCHEME']) && StringHelper::load($_SERVER['REQUEST_SCHEME'])
                                                                      ->toLowerCase()
                                                                      ->trimSpaces()
                                                                      ->isEquals('https')
                    ||

                    !empty($_SERVER['HTTPS']) && !StringHelper::load($_SERVER['HTTPS'])
                                                              ->toLowerCase()
                                                              ->trimSpaces()
                                                              ->isEquals('false')

                ) {
                    $secure = true;
                }

            }

            return $secure;
        }

        protected function __construct()
        {
            $this->REQUIRED_DATA['HOST'] = $this->getHostFromEnv();
            $this->REQUIRED_DATA['HTTPS'] = $this->isSecureFromEnv();
            $this->REQUIRED_DATA['GET'] = $this->getQueryFromEnv();
            $this->REQUIRED_DATA['PORT'] = $this->getPortFromEnv();
            $this->REQUIRED_DATA['PATH'] = $this->getPathFromEnv();

            if (!$this->isLoaded()) {
                throw new \Exception('Cant\'t initialize URL from this environment');
            }
        }

        public static function load()
        {
            return new static();
        }

        public function getData()
        {
            return $this->REQUIRED_DATA;
        }

        public function removeQuery()
        {
            $this->REQUIRED_DATA['GET'] = [];
            return $this;
        }

        public function isSecure()
        {
            return $this->REQUIRED_DATA['HTTPS'];
        }

        public function setPort($port)
        {
            $this->REQUIRED_DATA['PORT'] = $port;
            return $this;
        }

        public function removeQueryParam($key)
        {
            if (isset($this->REQUIRED_DATA['GET'][$key])) {
                unset($this->REQUIRED_DATA['GET'][$key]);
            }

            return $this;
        }

        public function setQueryParam($key, $value)
        {
            $this->REQUIRED_DATA['GET'][$key] = $value;

            return $this;
        }

        public function setPath($path)
        {
            $path = StringHelper::load($path)
                                ->ensureBeginningIs('/');

            if ($path->isEquals('/')) {
                $this->REQUIRED_DATA['PATH'] = '/';
            } else {
                $path->removeEnding('/');
                $this->REQUIRED_DATA['PATH'] = $path->get();
            }

            return $this;
        }

        public function removePath()
        {
            return $this->setPath('/');
        }

    }
}

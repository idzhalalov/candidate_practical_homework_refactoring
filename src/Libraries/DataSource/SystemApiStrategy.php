<?php

namespace Language\Libraries\DataSource;

use Language\ApiCall;
use Language\Dependencies;

class SystemApiStrategy implements DataSourceInterface
{
    protected $serviceProvider;

    public function __construct(ApiCall $serviceProvider = null)
    {
        $this->serviceProvider = $serviceProvider;
        if ($this->serviceProvider === null) {
            $this->serviceProvider = Dependencies::getClass('API_CALL');
        }
    }

    /**
     * Get content
     *
     * @param string $source
     * @param string $path
     * @param array  $params
     *
     * @throws DataSourceException
     *
     * @return bool
     */
    public function getData($source, $path, array $params = [])
    {
        $result = $this->serviceProvider::call(
            'system_api',
            'language_api',
            [
                'system' => $source,
                'action' => $path
            ],
            $params
        );
        $this->validateResponse($result);

        return $result;
    }

    protected function validateResponse($result)
    {
        // Error during the api call
        if ($result === false || !isset($result['status'])) {
            throw new DataSourceException('Error during the api call');
        }

        // Wrong response
        if ($result['status'] != 'OK') {
            throw new DataSourceException('Wrong response: '
                . (!empty($result['error_type']) ? 'Type(' . $result['error_type'] . ') ' : '')
                . (!empty($result['error_code']) ? 'Code(' . $result['error_code'] . ') ' : '')
                . ((string)$result['data']));
        }

        // Wrong content
        if ($result['data'] === false) {
            throw new DataSourceException('Wrong content!');
        }
    }
}

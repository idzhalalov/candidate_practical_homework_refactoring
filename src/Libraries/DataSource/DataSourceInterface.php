<?php

namespace Language\Libraries\DataSource;

interface DataSourceInterface
{
    public function getData($source, $path, array $params = []);
}

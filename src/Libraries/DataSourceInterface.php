<?php

namespace Language\Libraries;

interface DataSourceInterface
{
    public function getData($source, $path, array $params = []);
}

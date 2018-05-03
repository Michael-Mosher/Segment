<?php

namespace Segment\Model;

header("HTTP/1.1 404 File Not Found");
header("Content-Type: text/plain");
header("Content-Length: 0");

/*
interface ModelCategory
{
    abstract public function getFetchArg();
    abstract public function getInputOutputArguments(array $raw_arguments);
}*/
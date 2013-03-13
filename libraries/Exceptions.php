<?php namespace UserReputation\Exception;

/**
* Exception Classes
*/

class SysException extends \Exception
{
  const EC_UNKNOWN = 1;
}

class DBException extends \Exception
{
  const EC_QUERY = 1;
  const EC_INVALID_DATA = 2;
}

/**
* Exception Description Class
*/

class ExceptionDesc
{
  const EC_SUCCESS = 0;
  
  /*
   * GENERAL ERRORS
   */
  const EC_UNKNOWN = 1;
  const EC_OBJECT = 2;
  const EC_METHOD = 3;
  const EC_FILE = 4;
}
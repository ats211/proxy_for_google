<?php
require __DIR__ . "/vendor/autoload.php";

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Request;
use Psr\Http\Message\ResponseInterface;

const oAuthHost = 'https://accounts.google.com/';
const oAuthUri = 'o/oauth2/token';
try
{
	answer(getClient()->send(getRequest(getBody(getParams())), ['timeout' => 30]));
}
catch (GuzzleException $e)
{
	answerError($e);
}

/**
 * @param $params
 * @return string
 */
function getBody($params): string
{
	return implode(
		"&",
		array_map(
			static fn ($key, $value): string => $key . "=" . $value,
			array_keys($params),
			array_values($params)
		)
	);
}

/**
 * @param string $body
 * @return Request
 */
function getRequest(string $body): Request
{
	return new Request(
		'POST',
		oAuthUri,
		[
			'Connection' => 'close',
			'Accept' => '*/*',
			'Accept-Language' => 'en',
			'Content-Type' => 'application/x-www-form-urlencoded',
			'Content-Length' => mb_strlen($body),
		],
		$body
	);
}

/**
 * @return Client
 */
function getClient(): Client
{
	return new Client(['base_uri' => oAuthHost]);
}

/**
 * @return array
 */
function getParams(): array
{
	if (isset($_POST['code']))
	{
		$params['code'] = urlencode($_POST['code']);
	}
	if (isset($_POST['redirect_uri']))
	{
		$params['redirect_uri'] = urlencode($_POST['redirect_uri']);
	}
	if (isset($_POST['refresh_token']))
	{
		$params['refresh_token'] = $_POST['refresh_token'];
	}
	$params['grant_type'] = urlencode($_POST['grant_type']);


	$params['client_id'] = urlencode($_POST['client_id']);
	$params['client_secret'] = urlencode($_POST['client_secret']);

	return $params;
}

/**
 * @param ResponseInterface $response
 * @return void
 */
function answer(ResponseInterface $response): void
{
	header('HTTP/1.1 ' . $response->getStatusCode() . " " . $response->getReasonPhrase());
	//ToDo check response code
//	http_response_code((int)$response->getStatusCode());
	if ($response->getHeader('Content-Type'))
		header('Content-Type: ' . implode('; ', $response->getHeader('Content-Type')));

	header('X-Pavel: hello');


	echo $response->getBody()->getContents();
}

/**
 * @param GuzzleException $exception
 * @return void
 */
function answerError(GuzzleException $exception): void
{
	header("HTTP/1.1" . $exception->getCode(). " Bad Request");
	header('Content-Type: application/problem+json');

	$responseBody = '';
	try
	{
		$responseBody = json_encode([
			"type" => $_SERVER['HTTP_HOST'],
			"title" => $exception->getMessage(),
			"detail" => "You should wait and repeat the request later or contact support"
		], JSON_THROW_ON_ERROR);
	}
	catch (Exception $e)
	{
	}

	echo $responseBody;
}
























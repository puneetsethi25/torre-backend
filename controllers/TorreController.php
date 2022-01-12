<?php

namespace app\controllers;

use Yii;
use linslin\yii2\curl;

class TorreController extends \yii\rest\ActiveController
{
    public $modelClass  = '';
    public $baseUrl     = 'torre.co/api';
    public $bioUrl      = 'bio.torre.co/api';
    public $searchUrl   = 'search.torre.co';
    private $requestUrl = '';

    private function getPath($path, $schema = 'https')
    {
        return "{$schema}://{$this->requestUrl}{$path}";
    }


    private function makeRequest($path = '', $method = 'GET', $data = [])
    {
        $curl      = new curl\Curl();
        $full_path = $this->getPath($path);
        if('GET' == $method){
            $result = $curl->get($full_path, $data);
        }else{
            $result = $curl->post($full_path, $data);
        }
        if('string' == gettype($result)){
            $result = json_decode($result);
        }
        return $result ?? [];
    }

    private function setRequestUrl($requestUrl = false)
    {
        $this->requestUrl = $requestUrl ?? $this->baseUrl;
    }

    public function actionBio($username)
    {
        $this->setRequestUrl($this->bioUrl);
        $response = $this->makeRequest("/bios/{$username}", 'GET');
        return $this->asJson($response);
    }

    public function actionPeopleSuggestions($term = "")
    {
        $this->setRequestUrl($this->searchUrl);
        $response = $this->makeRequest("/people/name-suggestions?query={$term}&limit=3", 'GET');

        return $this->asJson($response);
    }

    public function actionSearchOpportunities()
    {
        $query_string = Yii::$app->getRequest()->queryString;
        $this->setRequestUrl($this->searchUrl);
        $response = $this->makeRequest("/opportunities/_search?{$query_string}", 'POST', Yii::$app->getRequest()->getBodyParams());

        return $this->asJson($response);
    }

    public function actionSearchPeople()
    {
        $this->setRequestUrl($this->searchUrl);
        $query_string = Yii::$app->getRequest()->queryString;
        $response = $this->makeRequest("/people/_search?{$query_string}", 'POST', Yii::$app->getRequest()->getBodyParams());

        return $this->asJson($response);
    }


    private function errorResponse($message, $code = 400)
    {

        Yii::$app->response->statusCode = $code;
        return $this->asJson(['error' => $message]);
    }
}

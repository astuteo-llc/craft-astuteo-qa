<?php
namespace astuteo\qa\console\controllers;

use astuteo\qa\services\InternalLinks;
use yii\console\Controller;
class CheckController extends Controller
{
    public function actionIndex() {
        InternalLinks::getAll();
        return 'complete';
    }

    public function actionLink() {
        InternalLinks::checkLinkById(50);
        return 'complete';
    }
    public function actionDeleteAll() {
        InternalLinks::deleteAll();
        return 'complete';
    }
}

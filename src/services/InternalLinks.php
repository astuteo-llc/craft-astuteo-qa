<?php

namespace astuteo\qa\services;
use GuzzleHttp\Exception\GuzzleException;
use yii\base\Component;
use craft\elements\Entry;
use craft\elements\Category;
use astuteo\qa\records\RunsRecord;
use astuteo\qa\records\BrokenLinksRecord;
use yii\db\StaleObjectException;

class InternalLinks extends Component
{
    /*
     * Checks all the links
     */
    public static function getAll() {
        $thisRun = new RunsRecord();
        $thisRun->type = 'internal';
        $thisRun->complete = false;
        $thisRun->save();


        $entries = self::checkEntries($thisRun->id);
        $categories =  self::checkCategories($thisRun->id);

        $normal = $entries['normal'] + $categories['normal'];
        $error = $entries['error'] + $categories['error'];
        $checked = $normal + $error;
        
        $thisRun->normal = $normal;
        $thisRun->error = $error;
        $thisRun->checked = $checked;

        $thisRun->complete = true;
        $thisRun->save();
    }
    /*
     * Checks a single link by record ID
     */
    public static function checkLinkById($id) {
        $record = BrokenLinksRecord::find()->where(['id' => $id])->one();
        if(!$record) {
            return;
        }
        self::checkLink($record->url, $record->runId);
    }

    public static function deleteAll() {
        $runs = RunsRecord::find()->all();
        foreach ($runs as $run) {
            self::deleteByRunId($run->id);
            $run->delete();
        }
    }

    /**
     * Delete links by run ID
     *
     * @throws StaleObjectException
     */
    public static function deleteByRunId($id) {
       $records = BrokenLinksRecord::find()->where(['runId' => $id])->all();
       if(!$records) {
           return;
       }
       foreach ($records as $record) {
           $record->delete();
       }
   }


    /*
     * Check entries with URLs
     */
    private static function checkEntries($runId = null) {
        $normal = 0;
        $error = 0;
        $entries = Entry::find()->all();
        foreach ($entries as $entry) {
            if($entry->url) {
                $link = self::checkLink($entry->url, $runId);
                if($link) {
                    $normal++;
                } else {
                    $error++;
                }
            }
        }
        return [
            'normal' => $normal,
            'error' => $error
        ];
    }

    /*
     * Check categories with URLs
     */
    private static function checkCategories($runId = null) {
        $normal = 0;
        $error = 0;
        $entries = Category::find()->all();
        foreach ($entries as $entry) {
            if($entry->url) {
                $link = self::checkLink($entry->url, $runId);
                if($link) {
                    $normal++;
                } else {
                    $error++;
                }
            }
        }
        return [
            'normal' => $normal,
            'error' => $error
        ];
    }

    private static function checkLink($url, $runId = null): bool
    {
        $client = new \GuzzleHttp\Client();
        try {
            $response = $client->request('GET', $url);
            return true;
        }  catch (GuzzleException $e) {
            $record = BrokenLinksRecord::find()->where(['url' => $url])->one();
            if(!$record) {
                $record = new BrokenLinksRecord();
            }
            $record->url = $url;
            $record->runId = $runId;
            $record->errorCode = '500';
            $record->save();
            return false;
        }
    }
}

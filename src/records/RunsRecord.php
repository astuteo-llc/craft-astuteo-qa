<?php

namespace astuteo\qa\records;

use craft\db\ActiveRecord;

class RunsRecord extends ActiveRecord
{
    public static function tableName()
    {
        return '{{%astuteo_qa_runs}}';
    }
}

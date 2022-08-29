<?php

namespace astuteo\qa\records;

use craft\db\ActiveRecord;

class BrokenLinksRecord extends ActiveRecord
{
    public static function tableName()
    {
        return '{{%astuteo_qa_broken_links}}';
    }
}

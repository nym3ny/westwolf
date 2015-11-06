<?php

namespace frontend\models;

use Yii;

/**
 * This is the model class for table "tasks".
 *
 * @property integer $task_id
 * @property integer $user_id
 * @property string $title
 * @property string $description
 * @property string $snapshot
 * @property string $priority
 * @property string $deadline
 *
 * @property User $user
 */
class Tasks extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'tasks';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'title', 'description', 'snapshot', 'priority', 'deadline'], 'required'],
            [['user_id'], 'integer'],
            [['description', 'priority'], 'string'],
            [['deadline'], 'safe'],
            [['title', 'snapshot'], 'string', 'max' => 255]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'task_id' => 'Task ID',
            'user_id' => 'User ID',
            'title' => 'Title',
            'description' => 'Description',
            'snapshot' => 'Snapshot',
            'priority' => 'Priority',
            'deadline' => 'Deadline',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }
}

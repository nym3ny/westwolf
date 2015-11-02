<?php

namespace backend\controllers;

use Yii;
use backend\models\User;
use backend\models\UserSearch;
use yii\filters\AccessControl;
use yii\helpers\ArrayHelper;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\UploadedFile;

/**
 * UserController implements the CRUD actions for User model.
 */
class UserController extends Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'actions' => [],
                        'allow' => true,
                        'roles' => ['admin'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['post'],
                ],
            ],
        ];
    }

    /**
     * Lists all User models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new UserSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single User model.
     * @param integer $id
     * @return mixed
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new User model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new User();

        /*
       * Upload avatar file, save
       */
        $postData=Yii::$app->request->post();
        $model->avatar_file = UploadedFile::getInstance($model,'avatar_file');
        if($model->avatar_file)
        {
            $model->avatar_file->saveAs('../../frontend/web/avatars/'.$postData['User']['username'].'.'.$model->avatar_file->extension);
            $model->avatar='../../frontend/web/avatars/'.$postData['User']['username'].'.'.$model->avatar_file->extension;
        }

        if ($model->load($postData) && $model->save()) {
           /*
           * Assign user role
           */
            if(isset($postData['User']['auth_assignment']))
                if($postData['User']['auth_assignment']!="")
                {
                    $auth = Yii::$app->authManager;
                    $authorRole = $auth->getRole($postData['User']['auth_assignment']);
                    $auth->assign($authorRole, $model->id);
                }
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('create', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Updates an existing User model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        /*
         * Prevent user to be modifyed by frontend hackers
         */
        $postData=Yii::$app->request->post();
        unset($postData['User']['username']);

        /*
         * Assign user role
         */
        if(isset($postData['User']['auth_assignment']))
        if($postData['User']['auth_assignment']!=$model->auth_assignment)
        {
            $auth = Yii::$app->authManager;
            $asig=$auth->getAssignments($id);
            if($asig)
            {
                $authorRole = $auth->getRole($model->auth_assignment);
                $auth->revoke($authorRole, $id);
            }
            $authorRole = $auth->getRole($postData['User']['auth_assignment']);
            $auth->assign($authorRole, $id);
        }

        /*
        * Upload avatar file, save
        */
        $model->avatar_file = UploadedFile::getInstance($model,'avatar_file');
        if($model->avatar_file)
        {
            $model->avatar_file->saveAs('../../frontend/web/avatars/'.$model->username.'.'.$model->avatar_file->extension);
            $model->avatar='../../frontend/web/avatars/'.$model->username.'.'.$model->avatar_file->extension;
        }

        if ($model->load($postData) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }


    /**
     * Deletes an existing User model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $model=$this->findModel($id);
        /*
         * before delete from DB we must delete avatar file.
         */
        @unlink($model->avatar);
        $model->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the User model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return User the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = User::findOne($id)) !== null) {
            /*
             * Asign auth_assignment for current user
            */
            $array=ArrayHelper::map(\backend\models\AuthAssignment::find()->where(['user_id'=>$id])->all(),'user_id','item_name');
            if($array) $model->auth_assignment = $array[$id];

            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}

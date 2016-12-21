<?php
/**
 * Created by PhpStorm.
 * User: david
 * Date: 2016/12/19
 * Time: 21:17
 * Email:liyongsheng@meicai.cn
 */

namespace app\modules\backend\models;


use app\models\Downloads;
use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;

class DownloadsSearch extends Downloads
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'type','admin_user_id'], 'integer'],
            [['title', 'image', 'description', 'create_at'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    /**
     * 创建时间
     * @return array|false|int
     */
    public function getCreateAt()
    {
        if(empty($this->create_at)){
            return null;
        }
        $createAt = is_string($this->create_at)?strtotime($this->create_at):$this->create_at;
        if(date('H:i:s', $createAt)=='00:00:00'){
            return [$createAt, $createAt+3600*24];
        }
        return $createAt;
    }
    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = Downloads::find();

        // add conditions that should always apply here
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort'=>['defaultOrder'=>['id'=>SORT_DESC]],
            'pagination' => ['pageSize'=>Yii::$app->params['pageSize']]
        ]);

        $this->load($params);
        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'type' => Downloads::TYPE_DOWNLOADS,
            'status' => $this->status,
            'admin_user_id' => $this->admin_user_id,
            'update_at' => $this->update_at,
        ]);

        $query->andFilterWhere(['like', 'title', $this->title])
            ->andFilterWhere(['like', 'description', $this->description]);
        $createAt = $this->getCreateAt();

        if(is_array($createAt)) {
            $query->andFilterWhere(['>=','create_at', $createAt[0]])
                ->andFilterWhere(['<=','create_at', $createAt[1]]);
        }else{
            $query->andFilterWhere(['create_at'=>$createAt]);
        }

        return $dataProvider;
    }
}
<?php

namespace modules\student\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use modules\student\models\School;

/**
 * SchoolSearch represents the model behind the search form about `modules\student\models\School`.
 */
class SchoolSearch extends School
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['HOSPCODE', 'VID', 'SCHOOLCODE', 'SCHOOLID', 'SCHOOLNAME', 'SCHOOLOWNER', 'SCHOOLTYPE', 'CLOSEDDATE', 'D_UPDATE'], 'safe'],
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
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = School::find();
        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'CLOSEDDATE' => $this->CLOSEDDATE,
            'D_UPDATE' => $this->D_UPDATE,
        ]);

        $query->andFilterWhere(['like', 'HOSPCODE', $this->HOSPCODE])
            ->andFilterWhere(['like', 'VID', $this->VID])
            ->andFilterWhere(['like', 'SCHOOLCODE', $this->SCHOOLCODE])
            ->andFilterWhere(['like', 'SCHOOLID', $this->SCHOOLID])
            ->andFilterWhere(['like', 'SCHOOLNAME', $this->SCHOOLNAME])
            ->andFilterWhere(['like', 'SCHOOLOWNER', $this->SCHOOLOWNER])
            ->andFilterWhere(['like', 'SCHOOLTYPE', $this->SCHOOLTYPE]);

        return $dataProvider;
    }
}

<?php

namespace modules\hrp\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use modules\hrp\models\Hrpinput;

/**
 * HrpinputSearch represents the model behind the search form about `modules\hrp\models\Hrpinput`.
 */
class HrpinputSearch extends Hrpinput
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['HOSPCODE', 'PID', 'GRAVIDA', 'RISK1', 'RISK2', 'RISK3', 'RISK', 'PLAN', 'OSM', 'INFO', 'STATUS'], 'safe'],
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
        $query = Hrpinput::find();
        $query->join('LEFT JOIN ','dhdc_module_hrp',
                                '   dhdc_module_hrp.HOSPCODE = dhdc_module_hrp_input.HOSPCODE
                                    AND dhdc_module_hrp.PID = dhdc_module_hrp_input.PID
                                    AND dhdc_module_hrp.GRAVIDA = dhdc_module_hrp_input.GRAVIDA'
                            );

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
        $query->andFilterWhere(['like', 'dhdc_module_hrp.HOSPCODE', $this->HOSPCODE])
            ->andFilterWhere(['like', 'dhdc_module_hrp.PID', $this->PID])
            //->orFilterWhere(['like', 'PID', $this->fullname])
            ->orFilterWhere(['like', 'dhdc_module_hrp.FNAME', $this->PID])
            ->orFilterWhere(['like', 'dhdc_module_hrp.LNAME', $this->PID])
            ->andFilterWhere(['like', 'GRAVIDA', $this->GRAVIDA])
            ->andFilterWhere(['like', 'RISK1', $this->RISK1])
            ->andFilterWhere(['like', 'RISK2', $this->RISK2])
            ->andFilterWhere(['like', 'RISK3', $this->RISK3])
            ->andFilterWhere(['like', 'RISK', $this->RISK])
            ->andFilterWhere(['like', 'PLAN', $this->PLAN])
            ->andFilterWhere(['like', 'OSM', $this->OSM])
            ->andFilterWhere(['like', 'INFO', $this->INFO])
            ->andFilterWhere(['like', 'STATUS', $this->STATUS]);

        return $dataProvider;
    }
}

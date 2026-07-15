<?php

namespace modules\dgis\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use modules\dgis\models\Home;

/**
 * HomeSearch represents the model behind the search form about `frontend\modules\dgis\models\Home`.
 */
class HomeSearch extends Home
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['HOSPCODE', 'HID', 'HOUSE_ID', 'HOUSETYPE', 'ROOMNO', 'CONDO', 'HOUSE', 'SOISUB', 'SOIMAIN', 'ROAD', 'VILLANAME', 'VILLAGE', 'TAMBON', 'AMPUR', 'CHANGWAT', 'TELEPHONE', 'NFAMILY', 'LOCATYPE', 'VHVID', 'HEADID', 'TOILET', 'WATER', 'WATERTYPE', 'GARBAGE', 'HOUSING', 'DURABILITY', 'CLEANLINESS', 'VENTILATION', 'LIGHT', 'WATERTM', 'MFOOD', 'BCONTROL', 'ACONTROL', 'CHEMICAL', 'OUTDATE', 'D_UPDATE'], 'safe'],
            [['LATITUDE', 'LONGITUDE'], 'number'],
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
        $query = Home::find();

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
            'LATITUDE' => $this->LATITUDE,
            'LONGITUDE' => $this->LONGITUDE,
            'OUTDATE' => $this->OUTDATE,
            'D_UPDATE' => $this->D_UPDATE,
        ]);

        $query->andFilterWhere(['like', 'HOSPCODE', $this->HOSPCODE])
            ->andFilterWhere(['like', 'HID', $this->HID])
            ->andFilterWhere(['like', 'HOUSE_ID', $this->HOUSE_ID])
            ->andFilterWhere(['like', 'HOUSETYPE', $this->HOUSETYPE])
            ->andFilterWhere(['like', 'ROOMNO', $this->ROOMNO])
            ->andFilterWhere(['like', 'CONDO', $this->CONDO])
            ->andFilterWhere(['like', 'HOUSE', $this->HOUSE])
            ->andFilterWhere(['like', 'SOISUB', $this->SOISUB])
            ->andFilterWhere(['like', 'SOIMAIN', $this->SOIMAIN])
            ->andFilterWhere(['like', 'ROAD', $this->ROAD])
            ->andFilterWhere(['like', 'VILLANAME', $this->VILLANAME])
            ->andFilterWhere(['like', 'VILLAGE', $this->VILLAGE])
            ->andFilterWhere(['like', 'TAMBON', $this->TAMBON])
            ->andFilterWhere(['like', 'AMPUR', $this->AMPUR])
            ->andFilterWhere(['like', 'CHANGWAT', $this->CHANGWAT])
            ->andFilterWhere(['like', 'TELEPHONE', $this->TELEPHONE])
            ->andFilterWhere(['like', 'NFAMILY', $this->NFAMILY])
            ->andFilterWhere(['like', 'LOCATYPE', $this->LOCATYPE])
            ->andFilterWhere(['like', 'VHVID', $this->VHVID])
            ->andFilterWhere(['like', 'HEADID', $this->HEADID])
            ->andFilterWhere(['like', 'TOILET', $this->TOILET])
            ->andFilterWhere(['like', 'WATER', $this->WATER])
            ->andFilterWhere(['like', 'WATERTYPE', $this->WATERTYPE])
            ->andFilterWhere(['like', 'GARBAGE', $this->GARBAGE])
            ->andFilterWhere(['like', 'HOUSING', $this->HOUSING])
            ->andFilterWhere(['like', 'DURABILITY', $this->DURABILITY])
            ->andFilterWhere(['like', 'CLEANLINESS', $this->CLEANLINESS])
            ->andFilterWhere(['like', 'VENTILATION', $this->VENTILATION])
            ->andFilterWhere(['like', 'LIGHT', $this->LIGHT])
            ->andFilterWhere(['like', 'WATERTM', $this->WATERTM])
            ->andFilterWhere(['like', 'MFOOD', $this->MFOOD])
            ->andFilterWhere(['like', 'BCONTROL', $this->BCONTROL])
            ->andFilterWhere(['like', 'ACONTROL', $this->ACONTROL])
            ->andFilterWhere(['like', 'CHEMICAL', $this->CHEMICAL]);

        return $dataProvider;
    }
}

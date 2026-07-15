<?php

namespace modules\student\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use modules\student\models\Student;

/**
 * StudentSearch represents the model behind the search form about `modules\student\models\Student`.
 */
class StudentSearch extends Student
{
    public $glob_find,$FNAME,$SCHOOLCODE,$HOSPCODE,$EDUCATIONYEAR;

    function __construct($SCHOOLCODE,$HOSPCODE,$EDUCATIONYEAR){ 
        $this->SCHOOLCODE = $SCHOOLCODE;
        $this->HOSPCODE = $HOSPCODE;
        $this->EDUCATIONYEAR = $EDUCATIONYEAR;
    }

    public function rules()
    {
        return [
            [['HOSPCODE', 'PID', 'SCHOOLCODE', 'EDUCATIONYEAR', 'CLASS', 'D_UPDATE', 'GRUDATE_DATE','FNAME','glob_find'], 'safe'],
            [['id'], 'integer'],
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
        $query = Student::find();
        $query->joinWith('sschool');
        $query->joinWith('personname');
        /*
        if( $this->SCHOOLCODE !=NULL && $this->HOSPCODE !=NULL){
            $query = $query->andWhere(['student.SCHOOLCODE'=>$this->SCHOOLCODE,'student.HOSPCODE'=>$this->HOSPCODE]);
        }*/
        if( $this->SCHOOLCODE !=NULL){
            $query = $query->andWhere(['student.SCHOOLCODE'=>$this->SCHOOLCODE]);
        }
        if( $this->HOSPCODE !=NULL){
            $query = $query->andWhere(['student.HOSPCODE'=>$this->HOSPCODE]);
        }
        if( $this->EDUCATIONYEAR !=NULL){
            $query = $query->andWhere(['student.EDUCATIONYEAR'=>$this->EDUCATIONYEAR]);
        }
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
            'D_UPDATE' => $this->D_UPDATE,
            'GRUDATE_DATE' => $this->GRUDATE_DATE,
            'id' => $this->id,
        ]);

        $query->andFilterWhere(['like', 'student.HOSPCODE', $this->HOSPCODE])
            //->andFilterWhere(['like', 'student.PID', $this->PID])
            ->andFilterWhere(['=', 'student.SCHOOLCODE', $this->SCHOOLCODE])
            ->andFilterWhere(['like', 'EDUCATIONYEAR', $this->EDUCATIONYEAR])
            ->andFilterWhere(['like', 'CLASS', $this->CLASS])
            ->orFilterWhere(['like', 'person.NAME', $this->PID])
            ->orFilterWhere(['like', 'person.LNAME', $this->PID])
            ->andFilterWhere(['like', 'school.HOSPCODE', $this->HOSPCODE]); 

        if ($this->glob_find) {
                $query->orFilterWhere(['like', 'school.SCHOOLNAME', $this->glob_find]) 
                ->orFilterWhere(['=', 'student.SCHOOLCODE', $this->SCHOOLCODE])
                ->orFilterWhere(['like', 'student.HOSPCODE', $this->glob_find])
                //->orFilterWhere(['like', 'school.SCHOOLCODE', $this->SCHOOLCODE]) 
                ->orFilterWhere(['like', 'person.NAME', $this->PID])
                ->orFilterWhere(['like', 'person.LNAME', $this->PID]) ;        
            }

        return $dataProvider;
    }
}

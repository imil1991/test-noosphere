<?php

namespace app\models;

use Yii;
use yii\base\Model;
use yii\web\Cookie;

/**
 * LoginForm is the model behind the login form.
 */
class LoginForm extends Model
{
    public $username;
    public $password;
    public $rememberMe = true;

    private $_user = false;


    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
            // username and password are both required
            [['username', 'password'], 'required'],
            // rememberMe must be a boolean value
            ['rememberMe', 'boolean'],
            // password is validated by validatePassword()
            ['password', 'validatePassword'],
        ];
    }

    /**
     * Validates the password.
     * This method serves as the inline validation for password.
     *
     * @param string $attribute the attribute currently being validated
     * @param array $params the additional name-value pairs given in the rule
     * @return bool
     */
    public function validatePassword($attribute, $params)
    {
        if (!$this->hasErrors()) {
            $user = $this->getUser();

            do {
                if($this->preventSpamCounter($attribute)){
                    return false;
                }

                if (!$user || !$user->validatePassword($this->password)) {
                    $this->addError($attribute, 'Incorrect username or password.');
                    return false;
                }

            } while (false);

            Yii::$app->response->cookies->remove('login-false');
            return true;
        }
    }

    /**
     * Anti-Spam based on the allowable number of redo actions
     * In case of exceeding this count of waiting mode turn on ($ timeout)
     * @param string $attribute the attribute currently being validated
     * @param string $key Key actions performed
     * @param integer $limit allowable number of repeats
     * @param integer $timeout cooldown attempts upon reaching a limit in seconds
     * @param boolean $setError establish error
     * @return boolean true - repeats limit reached, false - all ok
     */
    public function preventSpamCounter($attribute, $key = 'login-false', $limit = 3, $timeout = 300, $setError = true)
    {
        $limit = intval($limit);
        $timeout = intval($timeout);
        if ($limit <= 0 || $timeout <= 0) {
            return false;
        }

        $last = isset(Yii::$app->request->cookies[$key]) ?
            Yii::$app->request->cookies[$key]->value : '';
        $time = time() + $timeout;
        # the first execution of the action
        if (empty($last)) {
            Yii::$app->response->cookies->add(new Cookie([
                'name' => $key,
                'value' => 1,
                'expire' => $time,
            ]));
            Yii::$app->response->cookies->add(new Cookie([
                'name' => $key . '-expire',
                'value' => $time,
                'expire' => $time,
            ]));

            return false;
        }

        $counter = intval($last);
        if ($counter < $limit) {
            # Repeat: the limit is not reached
            Yii::$app->response->cookies->add(new Cookie([
                'name' => $key,
                'value' => $counter + 1,
                'expire' => $time,
            ]));
            Yii::$app->response->cookies->add(new Cookie([
                'name' => $key . '-expire',
                'value' => $time,
                'expire' => $time,
            ]));

            return false;
        } else {
            # waiting period: please wait
            if ($setError) {
                $this->addError($attribute, 'Попробуйте ещё раз через ' . (Yii::$app->request->cookies[$key . '-expire']->value - YII_NOW) . ' секунд');
            }

            return true;
        }
    }

    /**
     * Logs in a user using the provided username and password.
     * @return boolean whether the user is logged in successfully
     */
    public function login()
    {
        if ($this->validate()) {
            return Yii::$app->user->login($this->getUser(), $this->rememberMe ? 3600*24*30 : 0);
        }
        return false;
    }

    /**
     * Finds user by [[username]]
     *
     * @return User|null
     */
    public function getUser()
    {
        if ($this->_user === false) {
            $this->_user = User::findByUsername($this->username);
        }

        return $this->_user;
    }
}

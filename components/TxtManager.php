<?php
/**
 * Created by PhpStorm.
 * User: bona2
 * Date: 16.03.16
 * Time: 23:39
 */

namespace app\components;

use yii\base\Component;
use Yii;

class TxtManager extends Component {

    /**
     * @var string the path of the PHP script that contains the users.
     * This can be either a file path or a path alias to the file.
     * Make sure this file is writable by the Web server process if the users needs to be changed online.
     * @see loadFromFile()
     */
    public $itemFile = '@files/users.txt';


    private $users;

    /**
     * Initializes the application component.
     * This method overrides parent implementation by loading the users data
     * from txt file.
     */
    public function init()
    {
        parent::init();
        $this->itemFile = Yii::getAlias($this->itemFile);
        $this->load();
    }

    /**
     * Loads users data from persistent storage.
     */
    protected function load()
    {
        $this->users = [];
        $items = $this->loadFromFile($this->itemFile);
        foreach ($items as $item) {
            $this->users[] = $item;
        }
    }

    /**
     * Loads the users data from a txt file.
     *
     * @param string $file the file path.
     * @return array the users data
     */
    protected function loadFromFile($file)
    {
        if (is_file($file)) {
            $recoveredData = file_get_contents($file);
            return self::unserialize($recoveredData);
        } else {
            return [];
        }
    }

    /**
     * @inheritdoc
     * @return array
     */
    public function findOne($field, $value)
    {
        foreach ($this->users as $user) {
            if ($user[$field] === $value) {
                return $user;
            }
        }
        return array();
    }

    /**
     * Safe array unserialize
     * @param mixed $data data in serrialize
     * @param mixed $default data type
     * @return mixed
     */
    public static function unserialize($data, $default = array())
    {
        $data = strval($data);
        if (is_array($default)) {
            if (empty($data)) {
                return $default;
            }
            if (strpos($data, 'a:') !== 0) {
                if (preg_match('/^[a-zA-Z0-9\/\r\n+]*={0,2}$/', $data)) {
                    $data = base64_decode($data, true);
                    if (empty($data) || strpos($data, 'a:') !== 0) return $default;
                } else {
                    return $default;
                }
            }
            $data = unserialize($data);
            return ( ! empty($data) ? $data : $default );
        }
        return ( ! empty($data) ? unserialize($data) : $default );
    }

}
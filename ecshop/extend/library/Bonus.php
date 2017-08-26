<?php
/**
 *
 * 红包操作类
 *
 * @package   extend
 * @author    失眠小枕头 [levisun.mail@gmail.com]
 * @copyright Copyright (c) 2013, 失眠小枕头, All rights reserved.
 * @version   CVS: $Id: Bonus.php v1.0.1 $
 * @link      http://www.NiPHP.com
 * @since     2017/06/23
 */

class Bonus extends Model
{
    /**
     * 发送红包
     * @access public
     * @param  int $user_id       用户ID
     * @param  int $bonus_type_id 红包类型ID
     * @return boolean
     */
    public function sendBonus($user_id, $bonus_type_id)
    {
        $user_id = (int) $user_id;
        $bonus_type_id = (int) $bonus_type_id;

        if (!$user_id || !$bonus_type_id) {
            return false;
        }

        $data = array(
            'bonus_type_id' => $bonus_type_id,
            'user_id'       => $user_id,
            );

        $this->table('user_bonus')
        ->save($data);

        return true;
    }
}
<?php

namespace Preferences\Storage\MySQL;

use Krystal\Db\Sql\AbstractMapper;

final class ValueMapper extends AbstractMapper
{
    /**
     * {@inheritDoc}
     */
    public static function getTableName()
    {
        return self::getWithPrefix('user_pref_groups_items_values');
    }

    /**
     * Returns primary column name for current mapper
     * 
     * @return string
     */
    protected function getPk()
    {
        return 'id';
    }

    /**
     * Saves relation of users and their corresponding options
     * 
     * @param int $userId
     * @param array $params
     * @return boolean
     */
    public function saveRelation($userId, array $params)
    {
        return $this->syncWithJunction(UserRelationMapper::getTableName(), $userId, $params);
    }

    /**
     * Fetch values with group and item names
     * 
     * @return array
     */
    public function fetchComplete()
    {
        // Columns to be selected
        $columns = [
            ValueMapper::column('id'),
            ValueMapper::column('value'),
            GroupMapper::column('name') => 'group',
            ItemMapper::column('name') => 'item'
        ];

        $db = $this->db->select($columns)
                       ->from(self::getTableName())
                       // Table relation
                       ->leftJoin(ItemMapper::getTableName(), array(
                            ItemMapper::column('id') => self::getRawColumn('item_id')
                       ))
                       // Group relation
                       ->leftJoin(GroupMapper::getTableName(), array(
                            GroupMapper::column('id') => ItemMapper::getRawColumn('group_id')
                       ));

        return $db->queryAll();
    }

    /**
     * Fetch all values by associated item id
     * 
     * @param int $itemId
     * @param boolean $sort Whether sorting is required
     * @return array
     */
    public function fetchAll($itemId, $sort)
    {
        $db = $this->db->select('*')
                       ->from(self::getTableName())
                       ->whereEquals('item_id', $itemId)
                       ->orderBy($sort ? 'order' : array('id' => 'DESC'));

        return $db->queryAll();
    }
}

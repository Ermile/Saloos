<?php
namespace lib\mvc\models;

trait options
{
	/**
	 * read options table and fill pass needed value
	 * @param  string $_key  name of option
	 * @param  string $_type type of needed
	 * @return [type]        array or string depending on needed item
	 */
	public function sp_get_options($_key = null, $_type = 'value')
	{
		// $uid         = $this->login('id');
		$qry_result  = [];
		$qry_options = $this->sql()->table('options')
					->where('user_id', 'IS', 'NULL')
					->and('post_id', 'IS', "NULL")
					->and('option_cat', 'options')

					->groupOpen('g_status')
					->and('option_status', '=', "'enable'")
					->or('option_status', 'IS', "NULL")
					->or('option_status', "")
					->groupClose('g_status')
					->select()
					->allassoc();

		// get list of permissions
		$permList = $this->permList();
		$qry_result['permissions'] =
		[
			'value' => null,
			'meta'  => $permList
		];

		foreach ($qry_options as $key => $row)
		{
			if($row['option_key'] == 'permissions')
			{
				$myValue = $row['option_value'];
				$myMeta  = $permList;
			}
			else
			{
				$myValue = $row['option_value'];
				$myMeta  = $row['option_meta'];

				if(substr($myValue, 0,1) == '{')
				{
					$myValue = json_decode($myValue, true);
				}

				if(substr($myMeta, 0,1) == '{')
				{
					$myMeta = json_decode($myMeta, true);
				}
			}

			$qry_result[$row['option_key']] =
			[
				'value' => $myValue,
				'meta'  => $myMeta
			];
		}

		if($_key)
		{
			if($_type)
			{
				$qry_result = $qry_result[$_key][$_type];
			}
			else
			{
				$qry_result = $qry_result[$_key];
			}
		}
		// var_dump($qry_result);
		return $qry_result;
	}
}
?>

<?php
/**
 * Auto generated by prado-cli.php on 2008-03-18 12:28:13.
 */
class ModalTransaksiRecord extends TActiveRecord
{
	const TABLE='tbt_modal_transaksi';

	public $id;
	public $st_modal;
	public $modal;
	
	public $deleted;
	
	public static function finder($className=__CLASS__)
	{
		return parent::finder($className);
	}
}
?>
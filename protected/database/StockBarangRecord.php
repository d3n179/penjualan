<?php
/**
 * Auto generated by prado-cli.php on 2008-03-18 12:28:13.
 */
class StockBarangRecord extends TActiveRecord
{
	const TABLE='tbd_stok_barang';

	public $id;
	public $id_barang;
	public $stok;
	
	public $deleted;
	
	public static function finder($className=__CLASS__)
	{
		return parent::finder($className);
	}
}
?>
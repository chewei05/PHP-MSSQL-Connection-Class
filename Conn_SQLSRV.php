<?php
/*
程式名稱: SQLSRV 連線
程式說明: PHP 5.4(含) 後的版本捨棄 MSSQL 的連線方式，改用 SQLSRV 的連線方式，本 CLASS 為將相關連線及取得資料的函數進行整理與統合，以方便呼叫使用。
程式作者: Chewei Hu
建立日期: 2015/07/08 14:33
程式版本: v0.0.3
更新日期: 2015/09/02 10:11
注意事項: 如果使用的SQL Server資料庫是Big-5格式時，中文欄位請注意轉碼的問題，範例: SELECT id, RTRIM(CAST(name AS nchar)) FROM dbo.member　，中文欄位請先CAST成nchar格式，再做RTRIM。
*/

class connSQLSRV
{
	private $hostname = "127.0.0.1";
	private $database = "myDatabase";
	private $username = "myUser";
	private $password = "myPassword";
		
	private $charset  = "UTF-8";
	
	public $conn;						//連線Session
	public $result;					//查詢的資料集(Query Recordset)
	public $row;						//目前取得的資料Record
	public $totalFields;				//總欄數
	public $totalRows;				//總筆數
	public $query;						//欲查詢的SQL語法
	public $parameters = array();	//欲查詢的SQL語法的參數值，形態為 array()
	public $eof = true;				//資料集目前讀取位置(false:表示資料集已讀取完畢或是讀取到最後一筆)
	
	public function __construct() //建立連線
	{
		// PHP 5.4(含)以上的新連線方式，使用 SQLSRV
		$serverInfo = array( "Database"=>$this->database, "UID"=>$this->username, "PWD"=>$this->password, "CharacterSet"=>$this->charset);
		$this->conn = sqlsrv_connect( $this->hostname, $serverInfo);
	}
	
	public function runQuery( $sql_smt = NULL, $sql_parameters = NULL, $getFirstRecord = true ) //執行查詢
	{
		if ( !isset($sql_smt) )
			$sql_smt = $this->query;
		if ( !isset($sql_parameters) )
			$sql_parameters = isset($this->parameters) ? $this->parameters : array();
		
		if ( isset($sql_smt) && isset($sql_parameters) )
		{
			$this->result = sqlsrv_query( $this->conn, $sql_smt, $sql_parameters, array("Scrollable"=>"buffered") ); //加入array("Scrollable"=>"buffered")才能抓到得totalRows
			$this->totalRows = sqlsrv_num_rows($this->result);
			$this->totalFields = sqlsrv_num_fields($this->result);
			if ( $getFirstRecord ) // 自動讀取第一筆資料
				$this->row = sqlsrv_fetch_array($this->result);
		}
	}

	public function getAllRecord() //讀取所有資料
	{
		while ( $this->row = sqlsrv_fetch_array($this->result) )
		{
			for ( $i = 0, $str = ""; $i < $this->totalFields; $i++ )
			{
				if ( $i > 0 ) $str .= ',';
				$str .= $this->row[$i];
			}
			$str .= '<br />'; //★待處理
			echo $str;
		}
	}

	public function getNextRecord() //讀取下一筆資料
	{
		$this->row = sqlsrv_fetch_array($this->result);
		$this->eof = $this->row ? true : false;
	}
	
	public function freeRecordSet () //釋放查詢的資料集(Record Set)
	{
		sqlsrv_free_stmt( $this->result );
	}
	
	public function closeConnection () //關閉連線
	{
		sqlsrv_close( $this->conn );
	}
	
	public function debugConnection ( $conn ) //測試MSQL連線狀態
	{
		if ( isset($conn) )
		{
			if ( $conn ) {
				echo "MSSQL Connection Success!!!<hr />";  
			} else {
				echo "MSSQL Connection Error!!!<hr />";
				die(print_r(sqlsrv_errors(),true));
			}
		}
		else
		{
			echo "Missing Connection Parameter.<hr />";
		}
	}
	
	public function test () //Deubg用
	{
		echo "MS-SQL Host: ".$this->hostname."<br />";
		echo "Recordset totalRows=".$this->totalRows."<br />";
		echo "Recordset totalFields=".$this->totalFields."<br />";
	}

	public function end () //釋放資源、關閉連線
	{
		if ( isset($this->result) )
		{
			sqlsrv_free_stmt( $this->result );
			sqlsrv_close( $this->conn );
		}
	}
	public function __destruct () //釋放資源、關閉連線
	{
		if ( isset($this->result) )
		{
			sqlsrv_free_stmt( $this->result );
			sqlsrv_close( $this->conn );
		}
	}
	// End of Class
}

?>

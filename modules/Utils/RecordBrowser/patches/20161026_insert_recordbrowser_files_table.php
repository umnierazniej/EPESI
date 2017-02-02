<?php

defined("_VALID_ACCESS") || die('Direct access forbidden');

DB::CreateTable('recordbrowser_files','
			id I4 AUTO KEY,
			recordset C(255) NOTNULL,
			record_id I4 NOTNULL,
			field_name C(255) NOTNULL,
			filestorage_id I4 NOTNULL,
            created_on TS NOTNULL,
            created_by I4 NOTNULL,
            deleted I4'
);
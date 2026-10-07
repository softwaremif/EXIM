<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ApiShipmentController;
use App\Http\Controllers\Api\ApiSpbController;
use App\Http\Controllers\Api\ApiCmtController;
use App\Http\Controllers\Api\UtilityController;
use App\Http\Controllers\Api\DebitNoteController;
use App\Http\Controllers\Api\CommercialInvoiceController;
use App\Http\Controllers\Api\master\M_InvsController;
use App\Http\Controllers\Api\master\M_GudangController;
use App\Http\Controllers\Api\master\M_ByrController;
use App\Http\Controllers\Api\master\M_BillController;
use App\Http\Controllers\Api\ApiInvoiceController;
use App\Http\Controllers\Api\ShipplanController;

// Route::get('get-totalall/{spbpk}', [ApiSpbController::class, 'GetTotAll'])->name('api.get-tot-all');
// Route::post('/update-totalpcs', [ApiSpbController::class, 'updateTotPcs'])->name('api.update-totpcs');
// Route::post('/update-totkar', [ApiSpbController::class, 'updateTotKar'])->name('api.update-totkar');
// Route::post('/update-totbrt', [ApiSpbController::class, 'updateTotBrt'])->name('api.update-totbrt');
// Route::post('/update-totkub', [ApiSpbController::class, 'updateTotKub'])->name('api.update-totkub');

Route::get('get-totalsemua/{spbpk}', [ApiSpbController::class, 'GetTotSemua'])->name('api.get-totsemua');
Route::post('/update-totalsemua', [ApiSpbController::class, 'updateTotSemua'])->name('api.update-totsemua');

Route::get('get-spb', [ApiSpbController::class, 'GetSpb'])->name('api.get-spb');
Route::get('get-spb/{spbpk}', [ApiSpbController::class, 'GetSpbDetail'])->name('api.get-spb-detail');
Route::get('get-spb/doc-bc/{spbpk}', [ApiSpbController::class, 'GetSpbForDocBC'])->name('api.get-spb-doc-bc');
Route::post('/post-update-doc-bc', [ApiSpbController::class, 'PostUpdateDocBC'])->name('post.update-doc-bc'); 

Route::get('get-spbdt/{spbpk}', [ApiSpbController::class, 'GetSpbdt'])->name('api.get-spbdt');
Route::post('/insert-spbdt/{spbpk}', [ApiSpbController::class, 'InsertSpbdt'])->name('api.insert-spbdt');
Route::post('/update-spbdt', [ApiSpbController::class, 'UpdateSpbdt'])->name('api.update-spbdt');
Route::post('/edit-save-header-spbdt', [ApiSpbController::class, 'SaveEditHeaderSpbdt'])->name('api.edit-save-header-spbdt');
Route::post('/delete-spbdt', [ApiSpbController::class, 'DeleteSpbdt'])->name('api.delete-spbdt');
Route::post('spdt-copy', [ApiSpbController::class, 'SpbdtCopy'])->name('api.spbdt-copy');
Route::post('upload-excel/{spbpk}', [ApiSpbController::class, 'UploadExcel'])->name('api.spb-upload-excel');

// Look UP Orderlist  & Breakdown In SPB//
Route::get('get-lookup-order', [ApiSpbController::class, 'GetLookupOrder'])->name('api.get-lookup-order');
Route::post('add-lookup-order', [ApiSpbController::class, 'AddLookupOrder'])->name('api.add-lookup-order');
Route::get('get-lookup-order-detail-finance', [ApiCmtController::class, 'GetLookupDetailFinance'])->name('api.get-lookup-detail-finance');
Route::post('add-lookup-order-detail-finance', [ApiCmtController::class, 'AddLookupDetailFinance'])->name('api.add-lookup-detail-finance');

Route::get('get-data-kwitansi', [ApiCmtController::class, 'GetDataModalKwitansi'])->name('api.get-export-kwitansi');
Route::get('export-kwintansi', [ApiCmtController::class, 'export_kwitansi'])->name('export.kwitansi');
Route::get('export-kwintansi-by-modal', [ApiCmtController::class, 'export_kwitansi_by_modal'])->name('export.kwitans-by-modal');
Route::get('get-cmt-modal-export', [ApiCmtController::class, 'GetCmtModalExport'])->name('api.get-cmt-modal-export');
Route::get('get-export-list-shipment', [ApiCmtController::class, 'GetDataModalExportListShipment'])->name('api.get-export-list-shipment');
Route::get('export-list-shipment', [ApiCmtController::class, 'export_list_shipment'])->name('export.list-shipment');

Route::get('get-data-debitnote', [ApiCmtController::class, 'GetExportDebitNote'])->name('api.get-export-debitnote');
Route::get('export-excel-debitnote', [ApiCmtController::class, 'export_debitnote_by_modal'])->name('export.excel-debitnote');

Route::get('get-data-tagihan', [ApiCmtController::class, 'GetExportTagihan'])->name('api.get-export-tagihan');
Route::get('export-excel-tagihan', [ApiCmtController::class, 'export_tagihan_by_modal'])->name('export.excel-tagihan');

//list payment export
Route::get('pdf-list-payment-by-modal', [ApiCmtController::class, 'pdf_list_payment_by_modal'])->name('pdf-list-payment-by-modal');

Route::get('get-cmt', [ApiCmtController::class, 'GetCmt'])->name('api.get-cmt');
Route::get('get-cmt/{invpk}', [ApiCmtController::class, 'GetCmtDetail'])->name('api.get-cmt-detail');
Route::post('/edit-cmt', [ApiCmtController::class, 'EditCmt'])->name('api.edit-cmt');
Route::get('get-invoicedt/{invpk}', [ApiCmtController::class, 'GetInvoiceDetail'])->name('api.get-invoice-detail');
//Get List Shipment
Route::get('get-shipment', [ApiShipmentController::class, 'GetShipment'])->name('api.get-shipment');

Route::post('/insert-invoicedt/{invpk}', [ApiCmtController::class, 'InsertInvoicedt'])->name('api.insert-invoicedt');
Route::post('/update-invoicedt', [ApiCmtController::class, 'UpdateInvoicedt'])->name('api.update-invoicedt');
Route::post('/edit-save-header', [ApiCmtController::class, 'SaveEditHeader'])->name('api.edit-save-header');
Route::post('/delete-invoicedt', [ApiCmtController::class, 'DeleteInvDt'])->name('api.delete-invoice-detail');
Route::get('get-spb-for-invreq', [ApiCmtController::class, 'GetSpbForInvReq'])->name('api.get-spb-for-invreq');
Route::post('add-req-inv', [ApiCmtController::class, 'AddReqInv'])->name('api.add-req-inv');

/* Commercial Invoice */
Route::get('commercial-invoice/list', [CommercialInvoiceController::class, 'list'])->name('api.commercial-invoice.list');
Route::get('commercial-invoice/list-detail/{dbnotepk}', [CommercialInvoiceController::class, 'list_detail'])->name('api.commercial-invoice.list-detail');
Route::get('commercial-invoice/list-detail-main/{dbnotepk}', [CommercialInvoiceController::class, 'list_detail_perbaikan'])->name('api.commercial-invoice.list-detail-perbaikan');
Route::get('commercial-invoice/detail/{dbnotepk}', [CommercialInvoiceController::class, 'detail'])->name('api.commercial-invoice.detail');
Route::post('commercial-invoice/update/{dbnotepk}', [CommercialInvoiceController::class, 'update'])->name('api.commercial-invoice.update');
Route::post('commercial-invoice/update-main/{dbnotepk}', [CommercialInvoiceController::class, 'updatePerbaikan'])->name('api.commercial-invoice.update-perbaikan');
Route::post('commercial-invoice/remove-main', [CommercialInvoiceController::class, 'removePerbaikan'])->name('api.commercial-invoice.remove-perbaikan');
Route::post('commercial-invoice/remove/{dbnotepk}', [CommercialInvoiceController::class, 'remove'])->name('api.commercial-invoice.remove');
Route::get('commercial-invoice/destroy/{dbnotepk}', [CommercialInvoiceController::class, 'destroy'])->name('api.commercial-invoice.destroy');

/* Debit Note */
Route::get('debitnote/list', [DebitNoteController::class, 'list'])->name('api.debitnote.list');
Route::get('debitnote/detail/{listdbpk}', [DebitNoteController::class, 'detail'])->name('api.debitnote.detail');
Route::post('debitnote/store', [DebitNoteController::class, 'store'])->name('api.debitnote.store');
Route::post('debitnote/update/{listdbpk}', [DebitNoteController::class, 'update'])->name('api.debitnote.update');
Route::get('debitnote/delete/{listdbpk}', [DebitNoteController::class, 'delete'])->name('api.debitnote.delete');

/* Utility Core */
Route::get('utility/get-month', [UtilityController::class, 'get_month'])->name('api.utility.get-month');
Route::get('utility/get-shipper/{isFu}', [UtilityController::class, 'get_shipper'])->name('api.utility.get-shipper');

/* Start Data Master */
Route::get('get-gudang', [M_GudangController::class, 'get_gudang'])->name('api.get-gudang');
Route::get('get-ang', [M_GudangController::class, 'get_ang'])->name('api.get-ang');
Route::get('get-jang', [M_GudangController::class, 'get_jang'])->name('api.get-jang');
Route::get('get-fwd', [M_GudangController::class, 'get_fwd'])->name('api.get-fwd');
Route::get('get-emkl', [M_GudangController::class, 'get_emkl'])->name('api.get-emkl');

//Gudang CRUD
Route::get('get-gudang2', [M_GudangController::class, 'get_gudang2'])->name('api.get-gudang2');
Route::get('gudang/detail/{gudpk}', [M_GudangController::class, 'detail_gudang'])->name('api.gudang.detail');
Route::post('gudang/store', [M_GudangController::class, 'store_gudang'])->name('api.gudang.store');
Route::post('gudang/update/{gudpk}', [M_GudangController::class, 'update_gudang'])->name('api.gudang.update');

//Perusahaan Angkutan CRUD
Route::get('get-ang2', [M_GudangController::class, 'get_ang2'])->name('api.get-ang2');
Route::get('ang/detail/{angpk}', [M_GudangController::class, 'detail_ang'])->name('api.ang.detail');
Route::post('ang/store', [M_GudangController::class, 'store_ang'])->name('api.ang.store');
Route::post('ang/update/{angpk}', [M_GudangController::class, 'update_ang'])->name('api.ang.update');

//Jns Angkutan CRUD
Route::get('get-jang2', [M_GudangController::class, 'get_jang2'])->name('api.get-jang2');
Route::get('jang/detail/{jangpk}', [M_GudangController::class, 'detail_jang'])->name('api.jang.detail');
Route::post('jang/store', [M_GudangController::class, 'store_jang'])->name('api.jang.store');
Route::post('jang/update/{jangpk}', [M_GudangController::class, 'update_jang'])->name('api.jang.update');

//Fwd CRUD
Route::get('get-fwd2', [M_GudangController::class, 'get_fwd2'])->name('api.get-fwd2');
Route::get('fwd/detail/{fwdpk}', [M_GudangController::class, 'detail_fwd'])->name('api.fwd.detail');
Route::post('fwd/store', [M_GudangController::class, 'store_fwd'])->name('api.fwd.store');
Route::post('fwd/update/{fwdpk}', [M_GudangController::class, 'update_fwd'])->name('api.fwd.update');

//Emkl CRUD
Route::get('get-emkl2', [M_GudangController::class, 'get_emkl2'])->name('api.get-emkl2');
Route::get('emkl/detail/{emklpk}', [M_GudangController::class, 'detail_emkl'])->name('api.emkl.detail');
Route::post('emkl/store', [M_GudangController::class, 'store_emkl'])->name('api.emkl.store');
Route::post('emkl/update/{emklpk}', [M_GudangController::class, 'update_emkl'])->name('api.emkl.update');

//Buyer CRUD
Route::get('get-buyer', [M_GudangController::class, 'get_buyer'])->name('api.get-buyer');
Route::get('byr/detail/{byrpk}', [M_GudangController::class, 'detail_byr'])->name('api.byr.detail');
Route::post('byr/store', [M_GudangController::class, 'store_byr'])->name('api.byr.store');
Route::post('byr/update/{byrpk}', [M_GudangController::class, 'update_byr'])->name('api.byr.update');

/* Invs */
Route::get('master/invs/get-all', [M_InvsController::class, 'get_all'])->name('api.master.invs.get-all');
Route::get('master/invs/get-year', [M_InvsController::class, 'get_year'])->name('api.master.invs.get-year');

/* Byr */
Route::get('master/byr/get-all', [M_ByrController::class, 'get_all'])->name('api.master.byr.get-all');

/* Bill */
Route::get('master/bill/get-all', [M_BillController::class, 'get_all'])->name('api.master.bill.get-all');
Route::post('master/bill/store', [M_BillController::class, 'store'])->name('api.master.bill.store');

Route::post('invoice-upload-excel/{invspk}', [ApiInvoiceController::class, 'UploadExcel'])->name('api.invoice-upload-excel');
Route::post('invoice-delete-excel/{invspk}', [ApiInvoiceController::class, 'DeleteExcel'])->name('api.invoice-delete-excel');

/* End Data Master */

/*Shipment Plan*/
Route::get('/getListExport', [ShipplanController::class, 'getListExport'])->name('api.shipplan.getListExport');
Route::post('/updateShipment', [ShipplanController::class, 'updateShipment']);
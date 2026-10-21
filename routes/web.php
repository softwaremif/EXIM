<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\LoginController;
use App\Http\Controllers\SpbController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\DebitNoteController;
use App\Http\Controllers\CommercialInvoiceController;
use App\Http\Controllers\KontrakKerjaController;
use App\Http\Controllers\TabelController;
use App\Http\Controllers\EximExportController;
use App\Http\Controllers\EximImportController;
use App\Http\Controllers\Packing\PackingController;
use App\Http\Controllers\Shipplan\ShipplanController;


Route::get('/', [LoginController::class, 'index'])->name('login.index');
Route::post('login', [LoginController::class, 'login'])->name('login');
Route::get('loading', [LoginController::class, 'loading'])->name('loading');
Route::get('logout', [LoginController::class, 'logout'])->name('logout');

Route::middleware(['check'])->group(function () {
    Route::post('updtpswdb', [LoginController::class, 'updtpswdb'])->name('updtpswdb');
    Route::post('updtpswd2b', [LoginController::class, 'updtpswd2b'])->name('updtpswd2b');
    Route::get('expired-change-date', [LoginController::class, 'ExpiredChangeDate'])->name('ExpiredChangeDate');
    Route::get('password', [LoginController::class, 'password'])->name('password');

    // /*EXPORT*/
    // Route::get('/exim-export', [EximExportController::class, 'index'])->name('exim-export.index');
    // Route::get('/packing', [PackingController::class, 'index'])->name('packing.index');
    // Route::get('/packing/input/{popk}', [PackingController::class, 'inputPacking'])->name('packing.input');
    // Route::get('/packing/{popk}/breakdown-summary', [PackingController::class, 'breakdownSummary'])->name('packing.breakdownSummary');

      /*EXPORT*/
    Route::get('/exim-export', [EximExportController::class, 'index'])->name('exim-export.index');
    Route::get('/exim-export/packing', [PackingController::class, 'index'])->name('exim-export.packing.index');
    Route::get('/buyer-list', [PackingController::class, 'buyerList'])->name('buyer-list');
    Route::get('/packing/list', [PackingController::class, 'getList'])->name('packing.list');

    /* Menu Export Import */
    Route::get('/order', [EximImportController::class, 'order'])->name('exim.import.order');
    Route::get('/order/data', [EximImportController::class, 'orderData'])->name('exim.import.order.data');
    Route::get('/ship', [EximImportController::class, 'ship'])->name('exim.import.ship');
    Route::get('/ship/data', [EximImportController::class, 'shipData'])->name('exim.import.ship.data');
    Route::post('/exim-import/get-po-ship', [EximImportController::class, 'getPoShip'])->name('exim.import.get-po-ship');
    Route::get('/exim/import/overprice/{po}', [EximImportController::class, 'getOverPrice'])->name('exim.import.overprice');
    Route::get('/exim/import/overqty/{po}', [EximImportController::class, 'getOverQty']);
    Route::get('/detpopur/{po}', [EximImportController::class, 'detpopur'])->name('exim.import.detpopur');
    Route::get('/shipdet_detpopur/{po}', [EximImportController::class, 'shipdetDetpopur'])->name('exim.import.shipdet_detpopur');
    Route::get('/exim/import/detail-po/{importpk}', [EximImportController::class, 'getDetailPo'])->name('exim.import.detail_po');
    Route::post('/ship/{importpk}/change-status', [EximImportController::class, 'changeShipStatus'])->name('exim.import.ship.changeStatus');
    Route::post('/ship/{importpk}/cancel', [EximImportController::class, 'cancelShip'])->name('exim.import.ship.cancel');
    Route::get('/exim/import/ship/view/{importpk}', [EximImportController::class, 'viewShip'])->name('exim.import.ship.view');
    Route::put('/exim/import/ship/{importpk}/update', [EximImportController::class, 'updateShipmentPlan'])->name('exim.import.ship.update');
    Route::post('/exim/import/ship/{importdtpk}/update-qty', [EximImportController::class, 'updateShipmentItemQty'])->name('exim.import.ship.updateQty');
    Route::delete('/exim/import/ship/material/{importdtpk}', [EximImportController::class, 'deleteShipmentMaterial'])->name('exim.import.ship.deleteMaterial');
    Route::get('/exim/import/ship/materials-for-po/{popk}', [EximImportController::class, 'getMaterialsForPo'])->name('exim.import.ship.materialsForPo');
    Route::post('/exim/import/ship/{importpk}/add-materials', [EximImportController::class, 'addMaterialsToShipment'])->name('exim.import.ship.addMaterials');
    Route::get('/exim/import/ship/options', [EximImportController::class, 'shipOptions'])->name('exim.import.ship.options');
    Route::get('/exim/import/notifications', [EximImportController::class, 'getApprovedPoNotifications'])->name('exim.import.notifications');
    Route::get('/exim/import/notifications/view/{popk}', [EximImportController::class, 'viewApprovedPo'])->name('exim.import.notifications.view');
    Route::get('/exim/import/revised-po-list', [EximImportController::class, 'getRevisedPoList'])->name('exim.import.revisedPoList');
    Route::post('/exim/import/clear-notice/{popk}', [EximImportController::class, 'clearOrderNotice'])->name('exim.import.clearNotice');
    Route::post('/exim/import/ship/create-draft', [EximImportController::class, 'createShipmentDraft'])->name('exim.import.ship.createDraft');
    Route::post('/exim/import/ship/{importpk}/confirm', [EximImportController::class, 'confirmShipmentPlan'])->name('exim.import.ship.confirm');
    Route::get('/exim/import/shipment-logs/{importpk}', [EximImportController::class, 'getShipmentLogs'])->name('exim.import.shipmentLogs');
    Route::delete('/exim/import/ship/draft/{importpk}', [EximImportController::class, 'deleteShipmentDraft'])->name('exim.import.ship.deleteDraft');
    Route::get('/ship/export', [EximImportController::class, 'exportShip'])->name('exim.import.ship.export');
    Route::post('/exim/import/order/sync-status', [EximImportController::class, 'syncPoStatusManual'])->middleware('throttle:10,1')->name('exim.import.order.syncStatus');

    /* Menu Commercial Invoice */
    Route::get('commercial-invoice/index', [CommercialInvoiceController::class, 'index'])->name('commercial-invoice.index');
    Route::get('commercial-invoice/index/main', [CommercialInvoiceController::class, 'indexPerbaikan'])->name('commercial-invoice.index-perbaikan');
    Route::get('commercial-invoice/store', [CommercialInvoiceController::class, 'store'])->name('commercial-invoice.store');
    Route::get('commercial-invoice/store/main', [CommercialInvoiceController::class, 'storePerbaikan'])->name('commercial-invoice.store-perbaikan');
    Route::get('commercial-invoice/create/{dbnotepk}', [CommercialInvoiceController::class, 'create'])->name('commercial-invoice.create');
    Route::get('commercial-invoice/create/{dbnotepk}/main', [CommercialInvoiceController::class, 'createPerbaikan'])->name('commercial-invoice.create-perbaikan');
    Route::get('commercial-invoice/edit/{dbnotepk}', [CommercialInvoiceController::class, 'edit'])->name('commercial-invoice.edit');
    Route::get('commercial-invoice/edit/{dbnotepk}/main', [CommercialInvoiceController::class, 'editPerbaikan'])->name('commercial-invoice.edit-perbaikan');
    Route::get('commercial-invoice/pdf/{dbnotepk}', [CommercialInvoiceController::class, 'pdf'])->name('commercial-invoice.pdf');
    Route::get('commercial-invoice/excel/{dbnotepk}', [CommercialInvoiceController::class, 'excel'])->name('commercial-invoice.excel');

    /* Menu Debit Note */
    Route::get('debitnote/index', [DebitNoteController::class, 'index'])->name('debitnote.index');

    /* Menu Invoice Shipment */
    Route::get('list-invoice', [InvoiceController::class, 'index'])->name('list.invoice');
    Route::get('detail_invoice/{invspk}', [InvoiceController::class, 'detail_invoice'])->name('detail_invoice');
    // Route::get('/list-invoice/dtinvoice/{mode}/{noinv}/{tahun}', [InvoiceController::class, 'dtinvoice'])->name('dtinvoice');
    Route::get('/list-invoice/dtinvoice/{mode}/{invspk}/{userpk}', [InvoiceController::class, 'dtinvoice'])->name('dtinvoice');

    /* Menu Kontrak Kerja */
    Route::get('kontrak-kerja', [KontrakKerjaController::class, 'index'])->name('kontrak-kerja');
    Route::post('kontrakKerja/store', [KontrakKerjaController::class, 'store'])->name('kontrakKerja.store');
    Route::post('kontrakKerja/update/{kkpk}', [KontrakKerjaController::class, 'update'])->name('kontrakKerja.update');
    Route::get('kontrakKerja/detail/{kkpk}', [KontrakKerjaController::class, 'detail']);
    Route::post('subkon/create', [KontrakKerjaController::class, 'create'])->name('subkon.create');
    Route::get('/subkon/list', [KontrakKerjaController::class, 'listSubkon'])->name('subkon.list');
    Route::get('kontrakKerja/list', [KontrakKerjaController::class, 'list'])->name('kontrakKerja.list');

    //export
    Route::get('/exim-export/shipplan/index', [ShipplanController::class, 'index'])->name('shipplan.index');
    Route::get('/exim-export/shipplan/detail_ship/{exportpk}', [ShipplanController::class, 'detail_ship'])->name('shipplan.detail_ship');
});
//Tabel
Route::get('tabel-gudang', [TabelController::class, 'ListGudang'])->name('list.tab-gudang');
Route::get('tabel-perusahaan-angkutan', [TabelController::class, 'ListAng'])->name('list.tab-ang');
Route::get('tabel-angkutan', [TabelController::class, 'ListJang'])->name('list.tab-jang');
Route::get('tabel-forwarder', [TabelController::class, 'ListFwd'])->name('list.tab-fwd');
Route::get('tabel-emkl', [TabelController::class, 'ListEmkl'])->name('list.tab-emkl');
Route::get('tabel-buyer', [TabelController::class, 'ListBuyer'])->name('list.tab-byr');

Route::get('surat/index', [SpbController::class, 'index'])->name('index.spb');
Route::post('/post-create-inv', [SpbController::class, 'PostCreateInv'])->name('post.create-inv');
Route::post('/get-last-spb', [SpbController::class, 'GetLastSpb'])->name('get.lastspb');
Route::get('/add-spb/{spbpk}', [SpbController::class, 'AddSpb'])->name('add.spb');
Route::post('back-spb', [SpbController::class, 'BackSpb'])->name('back.spb');
Route::get('/get-gudang/{gudpk}', [SpbController::class, 'gudang'])->name('gudang');
Route::get('detail-spb/{spbpk}', [SpbController::class, 'DetailSpb'])->name('detail.spb');
Route::get('detail/{spbpk}/pdf', [SpbController::class, 'ExportPdf'])->name('ExportPdf');
Route::get('print-spb/{spbpk}', [SpbController::class, 'PrintSpb'])->name('print.spb');
Route::get('print-tips-stuffing/{spbpk}', [SpbController::class, 'PrintTips'])->name('print.tips');
Route::get('gudtest/{gudpk}', [SpbController::class, 'gudtest'])->name('gudtest');
//Menu Finance
Route::get('list-finance', [SpbController::class, 'ListFinance'])->name('list.finance');
Route::get('detail-finance/{invpk}', [SpbController::class, 'DetailFinance'])->name('detail.page.finance');
Route::get('detail/{spbpk}/finance', [SpbController::class, 'ExportPdfFinance'])->name('ExportPdfFinance');
Route::get('detail/{spbpk}/finance/pdf', [SpbController::class, 'ExportPdfFinanceDetail'])->name('ExportPdfFinanceDetail');

//Menu List Shipment
Route::get('list-shipment', [SpbController::class, 'ListShipment'])->name('list.shipment');

//Menu Invoice
Route::get('listinvoice', [InvoiceController::class, 'listinvoice'])->name('listinvoice');
Route::post('/input_noinv', [InvoiceController::class, 'input_noinv'])->name('input_noinv');
Route::get('buyer', [InvoiceController::class, 'buyer'])->name('buyer');
Route::get('lookupsom', [InvoiceController::class, 'lookupsom'])->name('lookupsom');
Route::post('get_lookup_som', [InvoiceController::class, 'get_lookup_som'])->name('get_lookup_som');
Route::get('lookupgis', [InvoiceController::class, 'lookupgis'])->name('lookupgis');
Route::post('get_lookup_gis', [InvoiceController::class, 'get_lookup_gis'])->name('get_lookup_gis');
Route::get('lookupinv', [InvoiceController::class, 'lookupinv'])->name('lookupinv');
Route::post('get_lookup_invsdt', [InvoiceController::class, 'get_lookup_invsdt'])->name('get_lookup_invsdt');
// Route::get('list_invsdt/{invspk}', [InvoiceController::class, 'list_invsdt'])->name('list_invsdt');
Route::post('update_list_dtinvoice', [InvoiceController::class, 'update_list_dtinvoice'])->name('update_list_dtinvoice');
Route::post('insert_list_dtinvoice/{invspk}', [InvoiceController::class, 'insert_list_dtinvoice'])->name('insert_list_dtinvoice');
Route::get('shipto_combobox/{byrpk}', [InvoiceController::class, 'shipto_combobox'])->name('shipto_combobox');
Route::get('shipto/{shiptopk}', [InvoiceController::class, 'shipto'])->name('shipto');
Route::post('submit_invs', [InvoiceController::class, 'submit_invs'])->name('submit_invs');
Route::post('back_listinvs', [InvoiceController::class, 'back_listinvs'])->name('back_listinvs');
Route::post('insert_shipto', [InvoiceController::class, 'insert_shipto'])->name('insert_shipto');
Route::post('edit_shipto', [InvoiceController::class, 'edit_shipto'])->name('edit_shipto');
Route::post('delete_invsdt', [InvoiceController::class, 'delete_invsdt'])->name('delete_invsdt');
Route::post('update_copy', [InvoiceController::class, 'update_copy'])->name('update_copy');
Route::get('get_invoice_cetak_pdf/{invspk}/{nilai}', [InvoiceController::class, 'get_invoice_cetak_pdf'])->name('get_invoice_cetak_pdf');
// Route::get('get_invoice_cetak_excel/{tahun}/{bulan}', [InvoiceController::class, 'get_invoice_cetak_excel'])->name('get_invoice_cetak_excel');
Route::get('/invoice/export/{tahun}/{bulan}', [InvoiceController::class, 'get_invoice_cetak_excel'])->name('invoice.export');
Route::get('getBdown', [InvoiceController::class, 'getBdown'])->name('getBdown');
Route::get('bulan', [InvoiceController::class, 'bulan'])->name('bulan');
Route::post('resi', [InvoiceController::class, 'resi'])->name('resi');
Route::post('status_td', [InvoiceController::class, 'status_td'])->name('status_td');
Route::post('cancelinvs', [InvoiceController::class, 'cancelinvs'])->name('cancelinvs');


    /*ship plan*/
    Route::get('/getListExport', [ShipplanController::class, 'getListExport'])->name('shipplan.getListExport');
    Route::post('/removeList', [ShipplanController::class, 'removeList'])->name('shipplan.removeList');
    Route::post('/exim-export/set_shipplan', [ShipplanController::class, 'set_shipplan'])->name('set_shipplan');
    Route::post('/exim-export/set_invs', [ShipplanController::class, 'set_invs'])->name('set_invs');
    Route::post('/exim-export/set_peb', [ShipplanController::class, 'set_peb'])->name('set_peb');
    Route::get('/get-fwd', [ShipplanController::class, 'getFwd'])->name('getFwd');
    Route::get('/get-vassal', [ShipplanController::class, 'getVassal'])->name('getVassal');
    Route::get('/get-plb', [ShipplanController::class, 'getPlb'])->name('getPlb');
    Route::get('/get-byrb', [ShipplanController::class, 'getByr'])->name('getByr');
    Route::get('/filterday', [ShipplanController::class, 'filterday'])->name('filterday');
    Route::post('/export/{exportpk}/containers', [ShipplanController::class, 'store_containers'])->name('export.containers.store');
    Route::get('/export/{exportpk}/{contpk}/available-cartons', [ShipplanController::class, 'available_cartons'])->name('export.cartons.available');
    Route::post('/containers/load', [ShipplanController::class, 'load_container'])->name('containers.load');
    Route::post('/containers/unload', [ShipplanController::class, 'unload_carton'])->name('containers.unload');
    Route::post('/containers/unload_all', [ShipplanController::class, 'unload_all_carton'])->name('containers.unload_all');
    Route::get('/export/{exportpk}/detail-json', [ShipplanController::class, 'detail_json'])->name('export.detail-json');
    Route::post('/shipment-plan/toggle-publish', [ShipplanController::class, 'togglePublish'])->name('shipment-plan.toggle-publish');
    Route::post('/shipment-plan/toggle-lock', [ShipplanController::class, 'toggleLock'])->name('shipment-plan.toggle-lock');
    Route::get('/export/{exportpk}/print', [ShipplanController::class, 'exportExcel'])->name('export.print');
    Route::get('/export/excel', [ShipplanController::class, 'exportExcel2'])->name('export.excel');
    Route::get('/exim-export/shipplan/{exportpk}/container-cards-refresh', [ShipplanController::class, 'refreshContainerCards'])->name('shipplan.containerCardsRefresh');
    Route::get('/containers/{contpk}/progress', [ShipplanController::class, 'containerProgress'])->name('containers.progress');
    Route::get('/export/si/{exportpk}', [ShipplanController::class, 'exportSI'])->name('exportSI');
    Route::get('/export/clp/{exportpk}', [ShipplanController::class, 'exportCLP'])->name('exportCLP');

    //set invoice baru
    Route::get('/invoice/options', [ShipplanController::class, 'invoiceOptions'])->name('invoice.options');
    Route::get('/invoice/generate', [ShipplanController::class, 'generateInvoiceNumber'])->name('invoice.generate');

    //hapus PO
    Route::post('/export/po-delete', [ShipplanController::class, 'deletePo'])->name('export.po-delete');

    //download PDF packing list (proxy ke finishing-packing)
    Route::get('/export/packing-list-pdf', [ShipplanController::class, 'packingListPdf'])->name('export.packing-list-pdf');

    //add PO
    Route::get('/export/{exportpk}/available-po', [ShipplanController::class, 'availablePo'])->name('export.available-po');
    Route::post('/export/po-add', [ShipplanController::class, 'addPo'])->name('export.po-add');   
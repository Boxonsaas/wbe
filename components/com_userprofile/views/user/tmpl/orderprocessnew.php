<?php
   /**
    * @version    CVS: 1.0.0
    * @package    Com_Userprofile
    * @author     madan <madanchunchu@gmail.com>
    * @copyright  2018 madan
    * @license    GNU General Public License version 2 or later; see LICENSE.txt
    */
   // No direct access
   
   
   
   $document = JFactory::getDocument();
$document->setTitle("Order Process in Boxon Pobox Software");
defined('_JEXEC') or die;
$session = JFactory::getSession();
$user=$session->get('user_casillero_id');
require_once JPATH_ROOT.'/modules/mod_projectrequestform/helper.php';
if(!$user){
    $app =& JFactory::getApplication();
    $app->redirect('index.php?option=com_register&view=login');
}

 $config = JFactory::getConfig();
 $backend_url=$config->get('backend_url');

// get domain details start

   $clientConfigObj = file_get_contents(JURI::base().'/client_config.json');
   $clientConf = json_decode($clientConfigObj, true);
   $clients = $clientConf['ClientList'];
   
   $domainDetails = ModProjectrequestformHelper::getDomainDetails();
   $CompanyId = $domainDetails[0]->CompanyId;
   $companyName = $domainDetails[0]->CompanyName;
   $domainEmail = $domainDetails[0]->PrimaryEmail;
   $domainName =  $domainDetails[0]->Domain;
   
   // get labels
$lang=$session->get('lang_sel');
    $res=Controlbox::getlabels($lang);
    $assArr = [];
    
    foreach($res->data as $response){
    $assArr[$response->id]  = $response->text;
    }

    // dynamic elements

    $res = Controlbox::dynamicElements('PendingShipments');
   $elem=array();
   foreach($res as $element){
      $elem[$element->ElementId]=array($element->ElementDescription,$element->ElementStatus,$element->is_mandatory,$element->is_default,$element->ElementValue);
   }

    // var_dump($elem['Hold'][1]);
    // exit;

   // enable / hide shipping info

   $shippingInfoStat = $elem['ShippingInfo'][1];

   // enable / disable grosswt column
if(strtolower($elem['GrossWtDisplay'][1]) == "act"){
   $Gross_weight_display = true;
}else{
   $Gross_weight_display = false;
}

// enable / disable grosswt column
if(strtolower($elem['DeclaredValDisplay'][1]) == "act"){
   $DeclaredValDisplay = true;
}else{
   $DeclaredValDisplay = false;
}

?>
  
  
<?php include 'dasboard_navigation.php' ?>
<script type="text/javascript" src="<?php echo JUri::base(true); ?>/components/com_userprofile/js/jquery.validate.min.js"></script>
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
<link rel="stylesheet" href="<?php echo JUri::base(true); ?>/components/com_userprofile/assets/css/styles.css">
<link rel="stylesheet" href="<?php echo JUri::base(true); ?>/components/com_userprofile/assets/css/demo.css">
<script type="text/javascript" src="https://js.squareupsandbox.com/v2/paymentform"></script>
<script src="https://www.paypal.com/sdk/js?client-id=<?php echo $ClientId; ?>&enable-funding=venmo&currency=USD" data-sdk-integration-source="button-factory"></script>

<style>

#viewImage .row:after {
  content: "";
  display: table;
  clear: both;
}



/* Expanding image text */
#imgalttext {
  position: absolute;
  bottom: 15px;
  left: 15px;
  color: white;
  font-size: 20px;
}


</style>
<!-- 
   <script src="https://code.jquery.com/jquery-1.12.4.js"></script>
   -->
<script type="text/javascript">
   var $joomla = jQuery.noConflict(); 
   $joomla(document).ready(function() {
      
      clientId = "<?php echo $ClientId; ?>";
       
      $joomla(".other_payment_type").prop("checked", true);
       $joomla('#dvPaymentInformation input[type=text]').on('keyup',function(){
        cardFormValidate();
    });
    
    $joomla("#colspantd1,#colspantd2,#colspantd3").addClass("colspan").attr("colspan",$joomla('.mainheader1 th').length);
        
    
    
    var AccountType = "<?php echo $AccountType; ?>";
    var ApiUrl = "<?php echo $ApiUrl; ?>";
   
    
     // end
    
    
     $joomla("#userprofileFormFive").validate({
                rules:  {
                        cardnumberStr: {
                        required: true,
                        minlength:15
                        },
                        MonthDropDownListStr: {
                        required: true
                        },
                        YearDropDownListStr: {
                        required: true
                        },
                        txtccnumberStr: {
                        required: true
                        },
                    },
                messages: {
                            cardnumberStr: {required:"<?php echo Jtext::_('COM_USERPROFILE_SHIP_CARD_NUMBER_ERROR');?>"},
                            MonthDropDownListStr: {required:"<?php echo Jtext::_('COM_USERPROFILE_SHIP_EXP_MONTH_ERROR');?>"},
                            YearDropDownListStr: {required:"<?php echo Jtext::_('COM_USERPROFILE_SHIP_EXP_YEAR_ERROR');?>"},
                            txtccnumberStr: {required:"<?php echo Jtext::_('COM_USERPROFILE_SHIP_CARD_CVV_ERROR');?>"}
                         },
                submitHandler: function(form) {
                     $joomla(".page_loader").show();
                     var feitem=[];
               $joomla("input[name='invFile[]']").each( function () {
                   var tem=$joomla(this).attr('id');
                   if($joomla(this).val()){
                       var file_data = $joomla(this).prop('files')[0];   
                       var form_data = new FormData();                  
                       form_data.append('file', file_data);
                       $joomla.ajax({
                       	url: "<?php echo JURI::base(); ?>index.php?option=com_userprofile&task=user.get_ajax_data&uploadflag=1&jpath=<?php echo urlencode  (JPATH_SITE); ?>&pseudoParam="+new Date().getTime(),
                   	    dataType: 'text',  // what to expect back from the PHP script, if anything
                           cache: false,
                           contentType: false,
                           processData: false,
                           data: form_data,                         
                           type: 'post',
                       	beforeSend: function() {
                             $joomla(".pagshipup").show();
                             $joomla('.pagshipdown').hide();
                             $joomla('#ord_ship #step3 .btn-primary').attr("type", "button");
                             $joomla('#ord_ship #step3 .btn-primary').attr("disabled", true);
                           },success: function(data){
                             $joomla('#ord_ship #step3 .btn-primary').attr("type", "submit");
                             $joomla('#ord_ship #step3 .btn-primary').attr("disabled", false);
                             $joomla(".pagshipup").hide();
                             $joomla('.pagshipdown').show();
                             feitem.push(tem+"-"+data);
                             
                             /** Debug **/
                             console.log(feitem);
                             
                             $joomla('input[name=paypalinvoice]').val(feitem); 
                           }
                       });
                   }
               }); 
                   var articlestrs=[];
               $joomla.each($joomla("input[name='articleStr[]']"), function(){
                   articlestrs.push($joomla(this).val());
               });    
               var pricestrs=[];
               $joomla.each($joomla("input[name='priceStr[]']"), function(){
                   pricestrs.push($joomla(this).val());
               }); 
               
               if($joomla('input[name="cc"]:checked').val() == "Stripe"){
               
                   setTimeout(function () {
                        ajaxurl = "<?php echo JURI::base(); ?>index.php?option=com_userprofile&task=user.get_ajax_data&amount="+$joomla('input[name="amount"]').val()+"&cardnumberStr="+$joomla('input[name="cardnumberStr"]').val()+"&txtccnumberStr="+$joomla('input[name="txtccnumberStr"]').val()+"&MonthDropDownListStr="+$joomla('select[name="MonthDropDownListStr"]').val()+"&YearDropDownListStr="+$joomla('select[name="YearDropDownListStr"]').val()+"&invidkStr="+$joomla('input[name="invidkStr"]').val()+"&qtyStr="+$joomla('input[name="qtyStr"]').val()+"&wherhourecStr="+$joomla('input[name="wherhourecStr"]').val()+"&user="+$joomla('input[name="user"]').val()+"&txtspecialinsStr="+$joomla('input[name="txtspecialinsStr"]').val()+"&cc=PPD&paymentgateway=Stripe&shipservtStr="+$joomla('input[name="shipservtStr"]').val()+"&consignidStr="+$joomla('input[name="consignidStr"]').val()+"&invf=&filenameStr=&articleStr="+articlestrs+"&priceStr="+pricestrs;
                        $joomla.ajax({
                       			url: ajaxurl,
                       			data: { "paymentgatewayflag":1,"ratetypeStr": $joomla('input[name="ratetypeStr"]').val(),"Conveniencefees":$joomla('input[name="Conveniencefees"]').val(),"addSerStr":$joomla('input[name="addSerStr"]').val(),"addSerCostStr":$joomla('input[name="addSerCostStr"]').val(),"companyId":$joomla('input[name="companyId"]').val(),"insuranceCost":$joomla('input[name="insuranceCost"]').val(),"extAddSer":$joomla('input[name="extAddSer"]').val(),"paypalinvoice":$joomla('input[name="paypalinvoice"]').val(),"length":$joomla("#lengthStr").val(),"width":$joomla("#widthStr").val(),"height":$joomla("#heightStr").val(),"grosswt":$joomla("#weightStr").val(),"volume":$joomla("#volStr").val(),"volumetwt":$joomla("#volmetStr").val(),"totalDecVal":$joomla('#totalDecVal').val(),"shipmentCost":$joomla('input[name=shipmentCost]').val(),"couponCodeStr":$joomla('#couponCodeStr').val(),"couponDiscAmt":$joomla('input[name=couponDiscAmt]').val(),"repackLblStr":$joomla('#repackLblStr').val()},
                       			dataType:"text",
                       			type: "get",
                                beforeSend: function() {
                                   
                                },
                                success: function(data){
                                    res = data.split(":");
                                    if(res[0] == 1){
                                    window.location.href="<?php echo JURI::base(); ?>index.php?option=com_userprofile&view=user&layout=response&res="+res[1];
                                    }else{
                                        $joomla(".page_loader").hide();
                                        $joomla(".paygaterrormsg").html(data);
                                    }
                                }
                           });
                           
                   }, 5000); 
               
               }else{
                   form.submit();
               }
   				      
                }
          
          });
          
          
    
    // authoriz.net error msg display
    
    var url_string = window.location.href;
    var url = new URL(url_string);
    var error_msg = url.searchParams.get("error");
    if(error_msg != null ){
        alert(error_msg);
         $joomla(".page_loader").show();
        window.location = "index.php?option=com_userprofile&view=user&layout=orderprocess";
    }
   
    
    function cardFormValidate(){
    var cardValid = 0;

    //card number validation
    $joomla('#card_number').validateCreditCard(function(result){
        if(result.valid){
            $joomla("#card_number").removeClass('required');
            cardValid = 1;
        }else{
            $joomla("#card_number").addClass('required');
            cardValid = 0;
        }
    });
      
    //card details validation
    var cardName = $joomla("#name_on_card").val();
    var expMonth = $joomla("#expiry_month").val();
    var expYear = $joomla("#expiry_year").val();
    var cvv = $joomla("#cvv").val();
    var regName = /^[a-z ,.'-]+$/i;
    var regMonth = /^01|02|03|04|05|06|07|08|09|10|11|12$/;
    var regYear = /^2017|2018|2019|2020|2021|2022|2023|2024|2025|2026|2027|2028|2029|2030|2031$/;
    var regCVV = /^[0-9]{3,3}$/;
    if (cardValid == 0) {
        $joomla("#card_number").addClass('required');
        $joomla("#card_number").focus();
        return false;
    }else if (!regMonth.test(expMonth)) {
        $joomla("#card_number").removeClass('required');
        $joomla("#expiry_month").addClass('required');
        $joomla("#expiry_month").focus();
        return false;
    }else if (!regYear.test(expYear)) {
        $joomla("#card_number").removeClass('required');
        $joomla("#expiry_month").removeClass('required');
        $joomla("#expiry_year").addClass('required');
        $joomla("#expiry_year").focus();
        return false;
    }else if (!regCVV.test(cvv)) {
        $joomla("#card_number").removeClass('required');
        $joomla("#expiry_month").removeClass('required');
        $joomla("#expiry_year").removeClass('required');
        $joomla("#cvv").addClass('required');
        $joomla("#cvv").focus();
        return false;
    }else if (!regName.test(cardName)) {
        $joomla("#card_number").removeClass('required');
        $joomla("#expiry_month").removeClass('required');
        $joomla("#expiry_year").removeClass('required');
        $joomla("#cvv").removeClass('required');
        $joomla("#name_on_card").addClass('required');
        $joomla("#name_on_card").focus();
        return false;
    }else{
        $joomla("#card_number").removeClass('required');
        $joomla("#expiry_month").removeClass('required');
        $joomla("#expiry_year").removeClass('required');
        $joomla("#cvv").removeClass('required');
        $joomla("#name_on_card").removeClass('required');
        return true;
    }
}
      
       // get payment gateways
       
        // $joomla('input[name="paymentmethod"]').on('click',function(e){
        //     $joomla("#paymentgatewaysDiv").show();
        //     var paymentmethod = $joomla(this).val();
        //     var ajaxurl = "<?php echo JURI::base(); ?>index.php?option=com_userprofile&task=user.get_ajax_data&paymentmethod="+paymentmethod;
          
        //     $joomla.ajax({
       	// 		url: ajaxurl,
       	// 		data: { "paymentmethodflag": 1 },
       	// 		dataType:"text",
       	// 		type: "get",
        //         beforeSend: function() {
        //             $joomla(".page_loader").show();
        //         },
        //         success: function(data){
        //             $joomla(".page_loader").hide();
        //             $joomla("#paymentgatewaysDiv").html(data);
        //         }
        //   });
           
        // });
        
        $joomla('.dvPaymentInformation select').on('change',function(e){
             $joomla(".paygaterrormsg").html("");
         });
         $joomla('.dvPaymentInformation input').keyup(function(e){
             $joomla(".paygaterrormsg").html("");
         });
       
       // End
       
       var previousPageText="<?php echo Jtext::_('COM_USERPROFILE_PREVIOUS_PAGE');?>";
       var nextPageText="<?php echo Jtext::_('COM_USERPROFILE_NEXT_PAGE');?>";
       var showEntText="<?php echo Jtext::_('COM_USERPROFILE_SHOW_ENTRIES');?>";
       var searchText="<?php echo Jtext::_('COM_USERPROFILE_SEARCH');?>";
       var showingEntriesText="<?php echo Jtext::_('COM_USERPROFILE_SHOWING_ENTRIES');?>";
       var showingEmptyEntriesText="<?php echo Jtext::_('COM_USERPROFILE_SHOWING_EMPTY_ENTRIES');?>";
       var nodataText = "<?php echo Jtext::_('COM_USERPROFILE_NO_DATA');?>";
       
       
   //     $joomla('#u_table').DataTable({
   //         "pagingType": "simple", // "simple" option for 'Previous' and 'Next' buttons only
   //         "ordering": false,
   //        "language": {
   //          "lengthMenu": showEntText,
   //          "search": searchText,
   //          "info": showingEntriesText,
   //          "infoEmpty": showingEmptyEntriesText,
   //          "emptyTable":nodataText,
   //          "paginate": {
   //            "previous": previousPageText,
   //            "next": nextPageText
   //          }
   //       },
   //          "order": [],
   //          "columnDefs": [
   //          { 
   //              "targets": [0], //first column / numbering column
   //              "orderable": false, //set not orderable
   //          }
   //          ]
            
   //  });  
    
     $joomla(document).on('change','input[name="invFile[]"]', function() {
        
            if(this.files.length > 1){
                 alert('<?php echo Jtext::_('Should not exceed more than 1 file.') ?>');
                 $joomla(this).val('');
            }
            
     });
    
       //$joomle('.dataTables_length').addClass('bs-select');
   
       history.pushState(null, null, location.href);
       window.onpopstate = function () {
           history.go(1);
       };
       //$joomla(".input-sm").html('<option value="25">25</option><option value="50">50</option><option value="100">100</option>');
   
       $joomla("input[name='txtQty']").live("blur",function(e){
           
           this.value = this.value.replace(/[^0-9]/g, '');
           if($joomla(this).val().length == 1 && $joomla(this).val() == 0 ||  $joomla(this).val() == ""){
                $joomla(this).val($joomla(this).closest('tr').find("input[name=ItemQtyTxt]").val()); 
           }
       });
       
       
       
       //select all uncheck one 
       
//       $joomla("#selectAll").change(function () {
//       $joomla("input:checkbox").prop('checked', $joomla(this).prop("checked"));
//      });

//      $joomla(".check_all_items").change(function () {
//      if($joomla(".check_all_items").length==$jooomla(".check_all_items:che").length)
//       $joomla("#selectAll").prop('checked', true);
//       else
//     $joomla("#selectAll").prop('checked', false);
// });
       
        // select all
        
        
       
      $joomla(document).on('click','#selectAll',function(e){
           // model changes
           $joomla('.shipAll').next().removeClass("not_same_error");
           $joomla("#error").html("");
           $joomla("#error").hide();
           $joomla("#confirmq").show();
           //end
           
           var ischecked= $joomla(this).is(':checked');
           //alert(ischecked);
           if(ischecked){
               $joomla('#ship_all').modal('show');
               $joomla('.shipAll').show();
               //$joomla('#step1 #j_table').show();
               $joomla('tr .ship:first-child').hide();
               
           }else{
               
                $joomla('input[name="txtId"]').each(function(){
                    var shipVal = $joomla(this).val();
                    var shipValArr = shipVal.split(":");
                    var qntVal = shipValArr[2];
                    //var qntVal = $joomla(this).closest('tr').find('td:eq(5)').text();
                    $joomla(this).closest('tr').find('input[name="txtQty"]').val(qntVal);
                    $joomla(this).prop( "checked", false );
                });
              $joomla(".check_all_items").prop("checked",false);
              $joomla(".check_all_items_repack").prop("checked",false);
              $joomla('#step1').hide(); 
              $joomla('.shipAll').hide();
              $joomla('tr .ship:first-child').show();
           }
       });
       
        $joomla('.ship_all_close').on('click',function(e){
           $joomla('tr .ship:first-child').show();
       });
       
       
       
       
       $joomla(document).on('click','.pagination li a',function(e){
          $joomla("input[name=selectAll]").prop( "checked", false );
                $joomla("#step1 #j_table").hide();
                $joomla(".btn-danger").trigger("click");
                $joomla("input[name=txtQty]").css("width","80px");
                });
       
       
       $joomla(document).on('change','select[name=j_table_length]',function(e){
           $joomla("input[name=txtQty]").css("width","80px");
           
       });
       $joomla("select[name=j_table_length]").val(50);
       $joomla("select[name=j_table_length]").trigger("change");
       
       
       $joomla(document).on('change','select[name=u_table_length]',function(e){
           $joomla("input[name=txtQty]").css("width","80px");
           
       });
       $joomla("select[name=u_table_length]").val(50);
       $joomla("select[name=u_table_length]").trigger("change");
       
       
       
        // ship all
       
        $joomla('.shipAll').on('click',function(){
            
            $joomla("#selectAll").prop( "checked", true );
            $joomla("#error").html("");
            $joomla("#error").hide();
            
            //$joomla(".ship:visible").trigger("click");
            $joomla("input[name=txtId]").prop("checked",false);
            $joomla("input[name=txtId]:visible").trigger("click");

            
            
            $joomla(".check_all_items").prop( "checked", false );
            $joomla(".check_all_items_repack").prop( "checked", false );

               $joomla(".check_all_items:visible").each(function(){
                  $joomla(this).trigger("click");
                  $joomla(this).prop("checked",true);
               });
               $joomla(".check_all_items_repack:visible").each(function(){
                  $joomla(this).trigger("click");
                  $joomla(this).prop("checked",true);
               });
            
            $joomla('#step1').show();
            $joomla('#step1 #j_table').show();
            if($joomla("#error").html().length > 0){
                 $joomla('#confirmq').hide();
                 $joomla('#shipAll').hide();
            }else{
               $joomla('#ship_all').modal('hide');
            }
        });
        
        
         // discard from shiplist
        
         $joomla(document).on('click','.discardShip',function(){
             
             var wrhsStr = $joomla(this).closest("tr").find(".warehouseStr").html();
             var disSno = $joomla(this).attr("data-sno");
             var discardRes = false;
             
                 $joomla("input[name='txtId']:checked").each( function () {
                     var txtidSno = $joomla(this).attr("data-sno");
                     var valueStr = $joomla(this).val();
                     var valueArr = valueStr.split(":");
                     
                     console.log(valueArr[1]+"#"+ wrhsStr + "#" + disSno + "#" + txtidSno );
                   
                     if(valueArr[1] == wrhsStr && disSno == txtidSno ){
                         $joomla(this).trigger('click');
                         discardRes = true;
                       //   var qntVal = $joomla(this).closest('tr').find('td:eq(5)').text();
                       //   $joomla(this).closest('tr').find('input[name="txtQty"]').val(qntVal);
                       //   $joomla(this).prop("checked",false);
                         
                     }
                     
                 });
                 
                 if(discardRes){
                     $joomla(this).closest("tr").hide();
                      var checkboxLen = $joomla("input[name='txtId']").filter(":checked").length;
                      
                      if(checkboxLen == 0){
                            $joomla('#step1').hide(); 
                            $joomla('#selectAll').prop("checked",false);
                            $joomla('.shipAll').hide();
                            
                            
                      }
                 }
        });
        
        // end
       
   $joomla("textarea[name='specialinstructionStr']").on('keyup',function(){
     $joomla("#count").text((250 - $joomla(this).val().length) + "/250" );
   });
   
       $joomla("input[name=txtQty]").css("width","80px");
       
       $joomla("input[name=txtQty]").live('blur',function(e){
           
           
           
           $joomla(this).closest('tr').find("input[name=ItemQtyEdit]").val($joomla(this).val());
          // $joomla(this).closest('tr').find("input[name=ItemQtyShip]").val($joomla(this).val());
          
          if(parseInt($joomla(this).val()) > parseInt($joomla(this).closest('tr').find("input[name=ItemQtyTxt]").val())){
             alert("Quantity will never update more than previous Quantity");
             $joomla(this).val($joomla(this).closest('tr').find("input[name=ItemQtyTxt]").val()); 
             $joomla($joomla(this).closest('tr').find("input[name=ItemQtyEdit]")).val($joomla(this).closest('tr').find("input[name=ItemQtyTxt]").val());
          }
          
          $joomla('#step1 #j_table tbody').remove(); 
          $joomla('#ord_ship #kk_table tbody').remove(); 
          
          
           if($joomla('#step1 #j_table tr').length>0){
                var tds='<tr>';
                var tdns='<tr>';
                var tdns2='<tr>';
                var whs=[];
                var idksn=[];
                var qtyl=[];
                var volres=[];
                var tyservice=[];
                var sr=[];
                var dt=[];
                var mrunits=[];
                var srhub=[];
                var ht=[];
                var wd=[];
                var lg=[];
                var wt=[];
                var dtunit=[];
                var whtr='';
                var bustype=[];
                var j=0;
                var invCount=0;
                var vol=[];
                var volmetwt=[];
               
                $joomla.each($joomla("input[name='txtId']:checked"), function(){
                   if($joomla(this).val()){
                   var loops=$joomla(this).val();
                   //console.log(loops);
                   var loop=loops.split(":");
                   for(i=0;i<loop.length;i++){
                       //if(loop[i]=="" || loop[i]==null){
                        //tds+="";   
                       //}
                       //else{
                        if(i==0){
                           tds+="<td>"+loop[i]+"</td>";
                           tdns+='<td><input type="text"  id="'+j+'" readonly  name="articleStr[]" class="form-control" value="'+loop[i]+'"></td>';
                           tdns2+='<td><input type="text"  id="'+j+'" readonly  name="article2Str[]" value="'+loop[i]+'"></td>';
                         }    
                        if(i==1){
                           //$joomla('#wherhourecStr').val(loop[i]); 
                           whs.push(loop[i]);
                           tds+="<td class='warehouseStr'>"+loop[i]+"</td>";
                        }    
                        if(i==2){
                           
                          qtyl.push($joomla(this).closest('tr').find('input[name="ItemQtyEdit"]').attr('value'));
                          tds+="<td>"+$joomla(this).closest('tr').find('input[name="ItemQtyEdit"]').attr('value')+"</td>";
                          tdns+="<td>"+$joomla(this).closest('tr').find('input[name="ItemQtyEdit"]').attr('value')+"</td>";
                          tdns2+="<td>"+$joomla(this).closest('tr').find('input[name="txtQty"]').attr('value')+"</td>";
                        }    
                        if(i==3){
                           $joomla('#trackingidStr').val(loop[i]);
                           tds+="<td>"+loop[i]+"</td>";
                           tdns+="<td>"+loop[i]+"</td>";
                           tdns2+="<td>"+loop[i]+"</td>";
                        }
                        if(i==4){
                           idksn.push(loop[i]); 
                           whtr=loop[i];
                        }
                        if(i==5){
                           $joomla('#ItemPriceStr').val(loop[i]);
                           //tdns+="<td>"+loop[i]+"</td>";
                           var quantity = $joomla(this).closest('tr').find('input[name="ItemQtyEdit"]').attr('value');
                           var totalCost = quantity*loop[24];
                           tdns+='<td><input type="text" class="form-control pricechange '+ loop[19] +'" data-insurance="'+loop[23]+'"   name="priceStr[]"  id="'+j+i+'"  value="'+totalCost.toFixed(2)+'"></td>';
                           tdns2+='<td><input type="text" name="price2Str[]"  id="'+j+i+'" readonly  value="'+loop[i]+'"></td>';
                        }
                        if(i==6){
                           $joomla('#costStr').val(loop[i]);
                           //tdns+="<td>"+loop[i]+"</td>";
                       }
                        if(i==8){
                           volres.push(loop[i]);
                        }
                        if(i==9){
                           tyservice.push(loop[i]);
                           //console.log(loop[i])
                        }
                        if(i==10){
                            
                           sr.push(loop[i]);
                           //console.log(loop[i])
                        }
                        if(i==11){
                           dt.push(loop[i]);
                           //console.log(loop[i])
                        }
                        if(i==12){
                           mrunits.push(loop[i]);
                           //console.log(loop[i])
                        }
                        //if(i==13){
                           //srhub.push(loop[i]);
                           //console.log(loop[i])
                        //}
                        if(i==13){
                           lg.push(loop[i]);
                        }
                        if(i==14){
                           wd.push(loop[i]);
                        }
                        if(i==15){
                           ht.push(loop[i]);
                        }
                        if(i==16){
                           wt.push(loop[i]);
                        }
                        if(i==17){
                           dtunit.push(loop[i]);
                        }
                        if(i==28){
                           bustype.push(loop[i]);
                        }
                        if(i==18){
                         var fileName = loop[i];
                         console.log("fileName:"+fileName);
                         var ext = fileName.substring(fileName.lastIndexOf('.') + 1);
                         if(ext =="GIF" || ext=="gif" || ext =="jpeg" || ext=="JPEG"  || ext=="pdf"  || ext =="PNG" || ext=="png"  || ext=="JPG"  || ext=="jpg" ){
                               invCount++;
                           var hrefs=loop[i];
                           hrefs=hrefs.split(' ').join('%20');
                           hrefs=hrefs.replace("#",":");
                           tdns+='<td> <div><div><label><?php echo Jtext::_('COM_USERPROFILE_SHIP_POPUP_INVOICE_UPLOAD');?></label></div><div><input type="file" class="upoadfe" name="invFile[]" multiple id='+whtr+'></div><div><p><?php echo Jtext::_('COM_USERPROFILE_SHIP_KTABLE_UPLOAD_EXT_TYPE_TXT');?></p></div></div><div class="clearfix"></div><a class="sfile" href="'+hrefs+'" target="_blank">(<?php echo Jtext::_('COM_USERPROFILE_INVOICE');?>)</a></td>';
                         }else{
                           tdns+='<td> <div><div><label><?php echo Jtext::_('COM_USERPROFILE_SHIP_POPUP_INVOICE_UPLOAD');?></label></div><div><input type="file" class="upoadfe" name="invFile[]"  multiple id='+whtr+'></div><div><p><?php echo Jtext::_('COM_USERPROFILE_SHIP_KTABLE_UPLOAD_EXT_TYPE_TXT');?></p></div></div></td>';
                         }
   
                         var file2Name = loop[i];
                         console.log("file2Name:"+file2Name);
                         var ext = file2Name.substring(file2Name.lastIndexOf('.') + 1);
                         if(ext =="GIF" || ext=="gif" || ext =="jpeg" || ext=="JPEG"  || ext=="pdf"  || ext =="PNG" || ext=="png"  || ext=="JPG"  || ext=="jpg" ){
                           var hrefs2=loop[i];
                           hrefs2=hrefs2.split(' ').join('%20');
                           hrefs2=hrefs2.replace("#",":");
                           tdns2+='<td> <div><div><label><?php echo Jtext::_('COM_USERPROFILE_SHIP_POPUP_INVOICE_UPLOAD');?></label></div><div><input type="file" class="upoadfe2" name="inv2File[]" multiple id='+whtr+'></div><div><p><?php echo Jtext::_('COM_USERPROFILE_SHIP_KTABLE_UPLOAD_EXT_TYPE_TXT');?></p></div></div><div class="clearfix"></div><a class="sfile" href="'+hrefs2+'" target="_blank">(<?php echo Jtext::_('COM_USERPROFILE_INVOICE');?>)</a></td>';
                         }else{
                           tdns2+='<td> <div><div><label><?php echo Jtext::_('COM_USERPROFILE_SHIP_POPUP_INVOICE_UPLOAD');?></label></div><div><input type="file" class="upoadfe2" name="inv2File[]" multiple id='+whtr+'></div><div><p><?php echo Jtext::_('COM_USERPROFILE_SHIP_KTABLE_UPLOAD_EXT_TYPE_TXT');?></p></div></div></td>';
                         }
   
                        }
                        if(i==29){
                            vol.push(loop[i]);
                        }
                        if(i==30){
                            volmetwt.push(loop[i]);
                        }
                        
       
                       j++;
                      //}
                 }
                 whs.join(", ");
                 idksn.join(", ");
                 volres.join(", ");
                 tyservice.join(", ");
                 sr.join(", ");
                 dt.join(", ");
                 mrunits.join(", ");
                 srhub.join(", ");
                 bustype.join(", ");
                 vol.join(", ");
                 volmetwt.join(", ");
   
                 lg.join(", ");
                 wd.join(", ");
                 ht.join(", ");
                 wt.join(", ");
                 dtunit.join(", ");
                 
                 qtyl.join(", ");
                 $joomla('#qtyStr').val(qtyl);
                 $joomla('#qty2Str').val(qtyl);
                 
                 $joomla('#wherhourecStr').val(whs);
                 $joomla('#wherhourec2Str').val(whs);
                 $joomla('#invidkStr').val(idksn);
                 $joomla('#invidk2Str').val(idksn);
                 $joomla('#volresStr').val(volres);
                 $joomla('#tyserviceStr').val(tyservice);
                 $joomla('#mrunitsStr').val(mrunits);
                 $joomla('#volStr').val(vol);
                 $joomla('#volmetStr').val(volmetwt);
                 
                 $joomla('#lengthStr').val(lg);
                 $joomla('#widthStr').val(wd);
                 $joomla('#heightStr').val(ht);
                 $joomla('#weightStr').val(wt);
                 $joomla('#dtunitStr').val(dtunit);
                 $joomla('#bustypeStr').val(bustype);
                 
                  
                 $joomla('#srhub').val(srhub);
                 $joomla('#srStr').val(sr);
                 $joomla('#dtStr').val(dt);
                 tds+='<td><span data-sno="'+loop[7]+'" class="discardShip"></span></td>';
                
                 tds+="</tr>";
                 tdns+="</tr>";
                 tdns2+="</tr>";
                 
               
                 
                }    
               });
       
               $joomla('#step1 #j_table:first').append(tds);
               //$joomla('#ord_ship #k_table:last').append(tdns);
               $joomla('#ord_ship #kk_table:last').append(tdns);
               $joomla('#ord_ship #ki_table:last').append(tdns2);
               $joomla('input[name=shipmentStr]').filter(':radio').prop('checked',false);
               $joomla('input[name=cc]').filter(':radio').prop('checked',false);
               $joomla('#divShipCOstOne').html('');
               $joomla('#ChangeShippingAddressNew').html();
               
               var sdf=$joomla('#txtbiladdress').attr("data-address");
               var sdatas=sdf.replace(/,/g,"<br>");
               $joomla('#ChangeShippingAddressNew').html(sdatas); 
               $joomla('#toaddressTxt').val(sdf);         
               $joomla('#destingaddreesStr').val(sdatas); 
               
             }
          
          
       });
       
       
        $joomla('input[name=txtService]').live( "click", function() {
           if($joomla('input[name=shipmentStr]:checked').val()=="undifined" || $joomla('input[name=shipmentStr]:checked').val()==null){
           alert("Please check shipping");
           return false;
          }
          if($joomla('input[name=shipmentNewStr]:checked').val()=="undifined" || $joomla('input[name=shipmentNewStr]:checked').val()==null){
           alert("Please check rate type");
           return false;
          }
           
           var ischecked= $joomla(this).is(':checked');
           
           var cosserv=$joomla(this).val();
           cosserv=cosserv.split(":");
           
           var totalview=0;
           if(ischecked){
               
               //$joomla(this).next().after('<span class="serCost_'+cosserv[1]+'" >&nbsp;&nbsp; $' + cosserv[0] + '</span>');
               
               totalview=$joomla('#totalamountDiv').html();
               $joomla('input[name=shipcostStr]').val(parseFloat(parseFloat(totalview)+parseFloat(cosserv[0])).toFixed(2));
               $joomla('#totalamountDiv').html(parseFloat(parseFloat(totalview)+parseFloat(cosserv[0])).toFixed(2));
               $joomla('input[name=amtStr]').val(parseFloat(parseFloat(totalview)+parseFloat(cosserv[0])).toFixed(2));
               $joomla('#shipmethodtotalStr').html(parseFloat(parseFloat(totalview)+parseFloat(cosserv[0])).toFixed(2));
               $joomla('#shipmethodStrValuetwo').html(parseFloat(parseFloat(totalview)+parseFloat(cosserv[0])).toFixed(2));
   
               var additionalcost=0;
               additionalcost=$joomla("#addserStr").html();
               $joomla('#addserStr').html(parseFloat(parseFloat(additionalcost)+parseFloat(cosserv[0])).toFixed(2));
               
               additionalcost1=0;
               additionalcost1=$joomla("#addserStr1").html();
               $joomla('#addserStr1').html(parseFloat(parseFloat(additionalcost1)+parseFloat(cosserv[0])).toFixed(2));
               
           }else{
               
             //$joomla('.serCost_'+cosserv[1]).remove();
               
               totalview=$joomla('#totalamountDiv').html();
               $joomla('input[name=shipcostStr]').val(parseFloat(parseFloat(totalview)-parseFloat(cosserv[0])).toFixed(2));
               $joomla('#totalamountDiv').html(parseFloat(parseFloat(totalview)-parseFloat(cosserv[0])).toFixed(2));
               $joomla('input[name=amtStr]').val(parseFloat(parseFloat(totalview)-parseFloat(cosserv[0])).toFixed(2));
               $joomla('#shipmethodtotalStr').html(parseFloat(parseFloat(totalview)-parseFloat(cosserv[0])).toFixed(2));
               $joomla('#shipmethodStrValuetwo').html(parseFloat(parseFloat(totalview)-parseFloat(cosserv[0])).toFixed(2));
               
               var additionalcost=0;
               additionalcost=$joomla("#addserStr").html();
               $joomla('#addserStr').html(parseFloat(parseFloat(additionalcost)-parseFloat(cosserv[0])).toFixed(2));
               
               additionalcost1=0;
               additionalcost1=$joomla("#addserStr1").html();
               $joomla('#addserStr1').html(parseFloat(parseFloat(additionalcost1)-parseFloat(cosserv[0])).toFixed(2));
           }  
           
           var serStrIds='';
                var serStrCost='';
               
           $joomla.each($joomla("input[name='txtService']:checked"), function(){ 
                   
                   var cosserv=$joomla(this).val();
                   cosserv=cosserv.split(":");
                   
                   serStrIds = serStrIds + cosserv[1] + ',';
                   serStrCost = serStrCost + cosserv[0] + ',';
                   
           });
           
           $joomla("#addSerStr").val(serStrIds);
           $joomla("#addSerCostStr").val(serStrCost);
           
       });  
       
       
        
   
       
       
       //if($joomla( "#orderdateTxt" ))
       //$joomla( "#orderdateTxt" ).datepicker({ maxDate: new Date });
       var tmp='';
       tmp=$joomla("#ord_edit .modal-body").html();
       
        $joomla.validator.addMethod(
   		"selectBox",
   		function(value, element) {
   			if (element.value == "none" || element.value == "0")
   			{
   				return false;
   			}
   			else {
   				return true;
   			}
   		},
   		""
   	);
       
   
       $joomla.validator.addMethod("alphanumeric", function(value, element) {
           return this.optional(element) || /^[a-zA-Z/ /]+$/.test(value);
       });
       
       $joomla('.return').click(function(){
          $joomla("#dfg").remove();  
           $joomla('#idk').val($joomla(this).data('id'));
           $joomla('input[name="qty"]').val($joomla(this).closest('tr').find('input[name="txtQty"]').attr('value'));
          $joomla(":checkbox").prop("checked", false);
          $joomla('#step1').hide(); 
          $joomla(".ship").click(true);  
           $joomla(".txtId").prop( "checked", false );
           $joomla.each($joomla("input[name='txtId']"), function(){ 
             $joomla(this).closest('tr').find('input[name="txtId"]').prop('checked',false);
           })
           $joomla("#ord_ship .ship").click(true); 
           $joomla.each($joomla(".wrchild"), function(){ 
               $joomla('.wrchild').val('+');
           }) 
           $joomla('#ChangeShippingAddress').hide(); 
          
       });
       $joomla('.keep').click(function(){
          $joomla("#dfg").remove();  
           $joomla('#idk2').val($joomla(this).data('id'));
           $joomla('input[name="qty"]').val($joomla(this).closest('tr').find('input[name="txtQty"]').attr('value'));
          $joomla(":checkbox").prop("checked", false);
          $joomla('#step1').hide(); 
          $joomla(".ship").click(true);  
           $joomla(".txtId").prop( "checked", false );
           $joomla.each($joomla("input[name='txtId']"), function(){ 
             $joomla(this).closest('tr').find('input[name="txtId"]').prop('checked',false);
           })
           $joomla("#ord_ship .ship").click(true); 
           $joomla.each($joomla(".wrchild"), function(){ 
               $joomla('.wrchild').val('+');
           })    
           $joomla('#ChangeShippingAddress').hide(); 
   
       });
       $joomla('.discardc').click(function(){
          $joomla("#dfg").remove();  
           $joomla('#idk3').val($joomla(this).data('id'));
           $joomla('input[name="qty"]').val($joomla(this).closest('tr').find('input[name="txtQty"]').attr('value'));
          $joomla(":checkbox").prop("checked", false);
          $joomla('#step1').hide(); 
          $joomla(".ship").click(true);  
           $joomla(".txtId").prop( "checked", false );
   
           $joomla.each($joomla("input[name='txtId']"), function(){ 
             $joomla(this).closest('tr').find('input[name="txtId"]').prop('checked',false);
           })
           $joomla("#ord_ship .ship").click(true); 
           $joomla.each($joomla(".wrchild"), function(){ 
               $joomla('.wrchild').val('+');
           })    
           $joomla('#ChangeShippingAddress').hide(); 
       });
       
       $joomla('#ord_ship .btn-primary:first').click(function(){
          $joomla("#loading-image").hide();
          $joomla('#step1').hide(); 
          $joomla('#ord_ship #step2').show();
          $joomla('#ord_ship #step3').hide();
       });
       $joomla('#ord_ship .btn-back').click(function(){
          $joomla('#shipmethodtotalStr').html($joomla('input[name="shipcostStr"]').val());
          $joomla('#step1').hide(); 
          $joomla('#ord_ship #step2').show();
          $joomla('#ord_ship #step3').hide();
       });
       
       
       $joomla('#ChangeShippingAddressStr').click(function(){
          loadadditionalusersData();
          $joomla('#ChangeShippingAddressHidden').html($joomla('#ChangeShippingAddressNew').html());
          $joomla('#dtStr2').val($joomla('#dtStr').val());
          $joomla('#ChangeShippingAddress').toggle(); 
       });
       
      $joomla('.btn-close1,#step1 .btn-danger,#step2 .btn-danger,#step3 .btn-danger,.not_same_error,.modal .btn-danger,.btn-grp1 .btn-danger').on("click",function(){
            $joomla("#custCouponCode").val("");
            $joomla(".couponErrorMsg").html("");
            $joomla(".cupn-cde").show();
            $joomla(".cupn-cde a").show();
            $joomla(".cupn-cdes").hide();
          $joomla(".paypalCreditDebit").hide();
          if($joomla("#exampleModal").css('display')=="block"){
              console.log('exampleModal');
          }else{
              $joomla("#dfg").remove();  
              $joomla(":checkbox").prop("checked", false);
              $joomla("input[name=paymentmethod]").prop("checked", false);
              $joomla('#paymentgatewaysDiv').hide();
              $joomla('#step1').hide(); 
              $joomla('#step1 #j_table tbody').remove();    
              $joomla('#ord_ship #k_table tbody').remove(); 
              $joomla('#ord_ship #ki_table tbody').remove(); 
              $joomla('#specialinstructionStr').val('');
              $joomla('#fnameTxt').val('');
              $joomla('#lnameTxt').val('');
              $joomla('#zipTxt').val('');
              $joomla('#addressTxt').val('');
              $joomla('#emailTxt').val('');
              $joomla('[name="consignidStr"]').val('');
              $joomla(".txtId").prop( "checked", false );
              if($joomla('input[name="shipment"]:checked').val()=="1"){
                  $joomla('#tabs2 #j_table tbody tr#dfg').remove();
              }else{
                  $joomla('#tabs2 #u_table tbody tr#dfg').remove();
              }   
          
               $joomla.each($joomla("input[name='txtId']"), function(){ 
                   
                   $joomla(this).closest('tr').find('input[name="txtId"]').prop('checked',false);
                   originalVal = $joomla(this).closest('tr').find('input[name="ItemQtyTxt"]').val();
                   $joomla(this).closest('tr').find('input[name="ItemQtyEdit"]').attr('value',originalVal);
                   $joomla(this).closest('tr').find('input[name="txtQty"]').val(originalVal);
                   $joomla(this).closest('tr').find('td:eq(2)').html(originalVal);
               
               })
               
               $joomla("#ord_ship .ship").click(true); 
               $joomla.each($joomla(".wrchild"), function(){ 
                   $joomla('.wrchild').val('+');
               })    
               $joomla('#ChangeShippingAddress').hide(); 
          }    
       });
       
       
        $joomla('.btn-danger').on("click",function(){
            
        var checkboxLen = $joomla("input[name='txtId']").filter(":checked").length;  
        
        if(checkboxLen == 0){
          
          if($joomla("#exampleModal").css('display')=="block"){
              console.log('exampleModal');
          }else{
              $joomla("#dfg").remove();  
              $joomla(":checkbox").prop("checked", false);
              $joomla("input[name=paymentmethod]").prop("checked", false);
              $joomla('#paymentgatewaysDiv').hide();
              $joomla('#step1').hide(); 
              $joomla('#step1 #j_table tbody').remove();    
              $joomla('#ord_ship #k_table tbody').remove(); 
              $joomla('#ord_ship #ki_table tbody').remove(); 
              $joomla('#specialinstructionStr').val('');
              $joomla('#fnameTxt').val('');
              $joomla('#lnameTxt').val('');
              $joomla('#zipTxt').val('');
              $joomla('#addressTxt').val('');
              $joomla('#emailTxt').val('');
              $joomla('[name="consignidStr"]').val('');
              $joomla(".txtId").prop( "checked", false );
              if($joomla('input[name="shipment"]:checked').val()=="1"){
                  $joomla('#tabs2 #j_table tbody tr#dfg').remove();
              }else{
                  $joomla('#tabs2 #u_table tbody tr#dfg').remove();
              }   
          
               $joomla.each($joomla("input[name='txtId']"), function(){ 
                   
                   $joomla(this).closest('tr').find('input[name="txtId"]').prop('checked',false);
                   originalVal = $joomla(this).closest('tr').find('input[name="ItemQtyTxt"]').val();
                   $joomla(this).closest('tr').find('input[name="ItemQtyEdit"]').attr('value',originalVal);
                   $joomla(this).closest('tr').find('input[name="txtQty"]').val(originalVal);
                   $joomla(this).closest('tr').find('td:eq(2)').html(originalVal);
               
               })
               
               $joomla("#ord_ship .ship").click(true); 
               $joomla.each($joomla(".wrchild"), function(){ 
                   $joomla('.wrchild').val('+');
               })    
               $joomla('#ChangeShippingAddress').hide(); 
          } 
          
        }else{
                $joomla("input[name='selectAll']").prop("checked",false);
        } 
          
       });
       
       
         $joomla('#kk_table input[name="priceStr[]"]').on("keyup",function(e){
          
           if($joomla(this).val().length == 1 && $joomla(this).val() == 0 ||  $joomla(this).val() == ""){
           	$joomla(this).val($joomla(this).prev().text());
           }
       });
       
       $joomla(document).on('blur', '#kk_table input[name="priceStr[]"]',function(){
           
               if($joomla(this).val() == 0 || $joomla(this).val() == 0.0 || $joomla(this).val() == 0.00 ||  $joomla(this).val() == ""){
               	$joomla(this).val($joomla(this).prev().text());
               	alert("Please enter a valid number");
               	return false;
               }
               $joomla('input[name="shipmentStr"]').prop("checked",false);
               $joomla('input[name="shipmentNewStr"]').prop("checked",false);
               $joomla('#divShipCOstOne').hide();
               $joomla('#divShipCOstTwo').hide();
               
       });
       
   
      $joomla(document).on("click",'.shipsubmit',function(){
          
           var checkboxLen = $joomla("input[name='txtId']").filter(":checked").length;
           
           if(checkboxLen){
               
                   $joomla('#ord_ship').modal("show");
                   $joomla('#divShipCOstOne').removeClass('rdo_cust shp-addnew1');
                   $joomla('#divShipCOstTwo').removeClass('rst_text shp-addnew1');
                   
                     var dvalue=[];
                       // $joomla.each($joomla("#kk_table input[name='priceStr[]'].True"), function(){
                        $joomla.each($joomla("#kk_table input[name='priceStr[]']"), function(){
                            //if($joomla(this).attr("data-insurance") == "1")
                                    dvalue.push($joomla(this).val());
                       }); 
                       
                       var total = 0;
                       for (var i = 0; i < dvalue.length; i++) {
                           total = parseFloat(total) + parseFloat(dvalue[i]);
                       }
                       
                       dtotal = total.toFixed(2);
                  
                  $joomla('input[value=DHL]').hide();
                  $joomla('input[value=DHL]').next().hide();
                  $joomla('#divShipCOstOne').html('');
                  $joomla('#divShipCOstTwo').html('');
                  
                  $joomla("#loading-image").hide();
                  $joomla('#ord_ship #step2').show();
                  $joomla('#ord_ship #step3').hide();
                  $joomla('#ord_ship #step4').hide();
                  //alert($joomla('input[name=shipment]:checked').closest(".rdo_rd1").find("label").text())
                  if($joomla('input[name=shipment]:checked').closest(".rdo_rd1").find("label").text()=="Ready To Ship J")
                   {
                      $joomla('#ord_ship #step2').hide();
                      $joomla('#ord_ship #step3').hide();
                      $joomla('#ord_ship #step4').show();
                   }
                   else{
                      $joomla('#ord_ship #step2').show();
                      $joomla('#ord_ship #step3').hide();
                       
                   }
                   var whsc=$joomla('#wherhourecStr').val();
                   whsc=whsc.split(",");
                   var shipetype=$joomla('#shipmenttype').val();
                   var ulks="<?php echo JURI::base(); ?>index.php?option=com_userprofile&task=user.get_ajax_data&paymenttype&dvalue="+ dtotal +"&wherhourec="+ whsc+"&invidk="+$joomla('#invidkStr').val() +"&qty="+$joomla('#qtyStr').val() +"&destination="+$joomla('#dtStr').val() +"&volres="+$joomla('#volresStr').val() +"&tyserv="+$joomla('#tyserviceStr').val() +"&munits="+$joomla('#mrunitsStr').val()+"&source="+$joomla('#srStr').val()+"&shiptype="+shipetype+"&user=<?php echo $user;?>&shippment2flag=1&jpath=<?php echo urlencode  (JPATH_SITE); ?>&pseudoParam="+new Date().getTime();
                  
                   $joomla.ajax({
           			url: ulks,
           			data: { "sd": $joomla(this).val() },
           			dataType:"html",
           			type: "get",
                       beforeSend: function() {
                           //$joomla(".pagshipup").show();
                           $joomla(".page_loader").show();
                           $joomla('.pagshipdown').hide();
                       },
                       success: function(data){
                           //$joomla(".pagshipup").hide();
                           $joomla(".page_loader").hide();
                           $joomla('.pagshipdown').show();
                          $joomla('#divShiprates').html(data);
                      }
                   });
                   
                   var shipetype=$joomla('#shipmenttype').val();
                   var urls="<?php echo JURI::base(); ?>index.php?option=com_userprofile&task=user.get_ajax_data&destination="+$joomla('#dtStr').val() +"&source="+$joomla('#srStr').val()+"&shiptype="+shipetype+"&user=<?php echo $user;?>&jpath=<?php echo urlencode  (JPATH_SITE); ?>&pseudoParam="+new Date().getTime();
                  
                   $joomla.ajax({
           			url: urls,
           			data: { "shippmenttypeflag": '1' },
           			dataType:"html",
           			type: "get",
                       beforeSend: function() {
                       },
                       success: function(data){
                         $joomla('#getServiceTypeDiv').html(data);
                     }
                   });
                   
           
           }else{
               alert("Please select at least one item");
           }    
   
       });
       
    // new pull req   
   
   
   $joomla(document).on('click','#goto_payment_scr',function(){
       
       var rateTypesText = $joomla('#divShipCOstTwo').text();
       var decValsErr=0;
       
       $joomla(".pricechange").each(function(){
           
           if($joomla(this).val() =="" || $joomla(this).val() =="0.00"){
               decValsErr=1;
               return false;
           }
           
       });
       
       
       if($joomla('#ChangeShippingAddressNew').html()==""){
               alert("Please select shipping address");
               return false;
              
          }else if($joomla('input[name=shipmentStr]:checked').val()=="undifined" || $joomla('input[name=shipmentStr]:checked').val()==null || $joomla('input[name=shipmentNewStr]:checked').val()=="undifined" || $joomla('input[name=shipmentNewStr]:checked').val()==null){
              
              if($joomla('input[name=shipmentStr]:checked').val()=="undifined" || $joomla('input[name=shipmentStr]:checked').val()==null){
                   alert("<?php echo Jtext::_('COM_USERPROFILE_SHIP_CHECK_SHIPPING') ?>");
              }
              else if($joomla('input[name=shipmentNewStr]:checked').val()=="undifined" || $joomla('input[name=shipmentNewStr]:checked').val()==null){
                   alert("<?php echo Jtext::_('COM_USERPROFILE_SHIP_CHECK_RATETYPE') ?>");
              }
           
           return false;
          }else if(rateTypesText.includes("Rates issue")){
              alert("<?php echo Jtext::_('COM_USERPROFILE_SHIP_RATE_ISSUE') ?>");
           return false;
          }else if(decValsErr == 1){
              alert("<?php echo Jtext::_('Please enter declared value') ?>");
              return false
          }else{
            
            $joomla('input[name="cc"]').prop("checked", false);
            $joomla('input[name="ccStr"]').prop("checked", false);
            $joomla('input[name="cardnumberStr"]').val('');
            $joomla('input[name="txtccnumberStr"]').val('');
            $joomla('input[name="txtNameonCardStr"]').val('');
            $joomla('.dvPaymentInformation').css('display','none');
            $joomla('#dvPaymentMethod').hide();          
            $joomla('select[name="MonthDropDownListStr"]').val('');
            $joomla('select[name="YearDropDownListStr"]').val('');
            $joomla('#specialinstructionDiv').html($joomla('textarea[name=specialinstructionStr]').val());
            $joomla('#txtspecialinsStr').val($joomla('textarea[name=specialinstructionStr]').val());
            $joomla('#step1').hide(); 
            $joomla('#ord_ship #step2').hide(); 
            $joomla('#ord_ship #step3').show();
          } 
          var serStrIds='';
          var serStrCost="";
          $joomla("input[name='txtService']:checked").each(function(){ 
                   var cosserv=$joomla(this).val();
                   cosserv=cosserv.split(":");
                   serStrIds = serStrIds + cosserv[1] + ',';
                   serStrCost = serStrCost + cosserv[0] + ',';
           });
            
           $joomla("#addSerStr").val(serStrIds);
           $joomla("#addSerCostStr").val(serStrCost);
           
   
            var unselList='';
            $joomla(".unselecteServices:checked").each(function(){
               
               var uncosserv=$joomla(this).val();
               var uncosservArr=uncosserv.split(":");
               unselList = unselList + uncosservArr[1] + ",";
                  
            });
            
            unselList = unselList.replace(/,+$/, '');
            $joomla("#extAddSer").val(unselList);
            $joomla(".other_payment_type").prop("checked", "checked");
           
          
   });
   
       
      
       
           
       $joomla('select[name="adduserlistStr"]').live('change',function(){
           $joomla('input[name=shipmentStr]').filter(':radio').prop('checked',false);
           $joomla('#divShipCOstTwo').html('');
           $joomla('#divShipCOstOne').removeClass('rdo_cust shp-addnew1');
           
           
           if($joomla(this).val()==0){
               $joomla('#dtStr').val($joomla('#dtStr2').val());
               $joomla('#ChangeShippingAddressNew').html(''); 
               $joomla('#ChangeShippingAddressNew').html($joomla('#ChangeShippingAddressHidden').html());
               $joomla("input[name='consignidStr']").val('');
   
               var whsc=$joomla('#wherhourecStr').val();
               whsc=whsc.split(",");
               var shipetype=$joomla('#shipmenttype').val();
               var ulks="<?php echo JURI::base(); ?>index.php?option=com_userprofile&task=user.get_ajax_data&paymenttype&wherhourec="+ whsc+"&invidk="+$joomla('#invidkStr').val() +"&qty="+$joomla('#qtyStr').val() +"&destination="+$joomla('#dtStr2').val() +"&volres="+$joomla('#volresStr').val() +"&tyserv="+$joomla('#tyserviceStr').val() +"&munits="+$joomla('#mrunitsStr').val()+"&source="+$joomla('#srStr').val()+"&shiptype="+shipetype+"&user=<?php echo $user;?>&shippment2flag=1&jpath=<?php echo urlencode  (JPATH_SITE); ?>&pseudoParam="+new Date().getTime();
              
               $joomla.ajax({
       			url: ulks,
       			data: { "sd": $joomla(this).val() },
       			dataType:"html",
       			type: "get",
                   beforeSend: function() {
                       $joomla(".pagshipup").show();
                       $joomla('.pagshipdown').hide();
                   },
                   success: function(data){
                       $joomla(".pagshipup").hide();
                       $joomla('.pagshipdown').show();
                      $joomla('#divShiprates').html(data);
                  }
               });   
   
   
          }else{    
   
               var vk=$joomla(this).val();
               vk=vk.split(":");
               var whsc=$joomla('#wherhourecStr').val();
               whsc=whsc.split(",");
               var shipetype=$joomla('#shipmenttype').val();
               var ulks="<?php echo JURI::base(); ?>index.php?option=com_userprofile&task=user.get_ajax_data&paymenttype&wherhourec="+ whsc+"&invidk="+$joomla('#invidkStr').val() +"&qty="+$joomla('#qtyStr').val() +"&destination="+vk[1] +"&volres="+$joomla('#volresStr').val() +"&tyserv="+$joomla('#tyserviceStr').val() +"&munits="+$joomla('#mrunitsStr').val()+"&source="+$joomla('#srStr').val()+"&shiptype="+shipetype+"&user=<?php echo $user;?>&shippment2flag=1&jpath=<?php echo urlencode  (JPATH_SITE); ?>&pseudoParam="+new Date().getTime();
              
               $joomla.ajax({
       			url: ulks,
       			data: { "sd": $joomla(this).val() },
       			dataType:"html",
       			type: "get",
                   beforeSend: function() {
                       $joomla(".pagshipup").show();
                       $joomla('.pagshipdown').hide();
                   },
                   success: function(data){
                       $joomla(".pagshipup").hide();
                       $joomla('.pagshipdown').show();
                      $joomla('#divShiprates').html(data);
                  }
               });   
   
    
               $joomla.ajax({
                   url: "<?php echo JURI::base(); ?>index.php?option=com_userprofile&task=user.get_ajax_data&adduserid="+vk[0] +"&adduserflag=1&jpath=<?php echo urlencode  (JPATH_SITE); ?>&pseudoParam="+new Date().getTime(),
       			data: { "shippmentid": vk[0] },
       			dataType:"html",
       			type: "get",
       			beforeSend: function() {
                       $joomla(".pagshipup").show();
                       $joomla('.pagshipdown').hide();
                       //$joomla("#loading-image2").show();
                       $joomla('#ord_ship #step2 .btn-primary').attr("disabled", true);
                  },success: function(data){
                       $joomla(".pagshipup").hide();
                       $joomla('.pagshipdown').show();
                       $joomla('#ord_ship #step2 .btn-primary').attr("disabled", false);
                       $joomla('#dtStr').val(vk[1]);
                       var sdata=data;
                       sdata=sdata.replace(",/gi","<br>");
                       $joomla('#ChangeShippingAddressNew').html(sdata); 
                       //$joomla('#ChangeShippingAddress').hide();
                       $joomla("#loading-image2").hide();
                       $joomla('input[name=shipmentStr]').filter(':radio').prop('checked',false);	
                       $joomla("#divShipCOstOne").html('');
                       $joomla("input[name='consignidStr']").val(vk[0]);
                       
       		    }
       		});
           }
       });
       
       
        $joomla(document).on('click','input[name=paymentmethod]',function(){
            
          if($joomla(this).val() == 'COD'){
                  $joomla('#userprofileFormFive').attr('action','');        
                  $joomla('#dvPaymentMethod').hide();
                  $joomla(".dvPaymentInformation").hide();
                  $joomla(".prepaid_method_sec").hide();
                  $joomla("input[name='prepaidMethod']").prop("checked",false);
                  $joomla('#shipmethodtotalStr').text($joomla('input[name="shipcostStr"]').val());
                  $joomla('input[name=amtStr]').val($joomla('input[name="shipcostStr"]').val());
                  $joomla('input[name=amount]').val($joomla('input[name="shipcostStr"]').val());
          }
            
        });
        
        // for authorization start
        
        $joomla(document).on('click','input[name=prepaidMethod]',function(){
            
        });
        
         // for authorization end
       
       $joomla(document).on('click','input[name=cc]',function(){
           
           
          if($joomla(this).val() == 'Paypal'){
              
              // credit / debit card integration changes
                 $joomla(".paypalCreditDebit").show();
                 $joomla(".final_btns").hide();
              //  end
              
              $joomla(".final_ship").attr("onclick","return onGetCardNonce(event)");
               
               $joomla(".dvPaymentInformation").hide();
                 var feitem=[];
               $joomla("input[name='invFile[]']").each( function () {
                   
                   var tem=$joomla(this).attr('id');
                   if($joomla(this).val()){
                       var file_data = $joomla(this).prop('files')[0];   
                       var form_data = new FormData();                  
                       form_data.append('file', file_data);
                       $joomla.ajax({
                       	url: "<?php echo JURI::base(); ?>index.php?option=com_userprofile&task=user.get_ajax_data&uploadflag=1&jpath=<?php echo urlencode  (JPATH_SITE); ?>&pseudoParam="+new Date().getTime(),
                   	    dataType: 'text',  // what to expect back from the PHP script, if anything
                           cache: false,
                           contentType: false,
                           processData: false,
                           data: form_data,                         
                           type: 'post',
                       	beforeSend: function() {
                             $joomla(".pagshipup").show();
                             $joomla('.pagshipdown').hide();
                             $joomla('#ord_ship #step3 .btn-primary').attr("type", "button");
                             $joomla('#ord_ship #step3 .btn-primary').attr("disabled", true);
                           },success: function(data){
                             $joomla('#ord_ship #step3 .btn-primary').attr("type", "submit");
                             $joomla('#ord_ship #step3 .btn-primary').attr("disabled", false);
                             $joomla(".pagshipup").hide();
                             $joomla('.pagshipdown').show();
                             feitem.push(tem+"-"+data);
                             
                             
                             /** Debug **/
                          
                             $joomla('input[name=paypalinvoice]').val(feitem); 
                           }
                       });
                   }
               }); 
               var articlestrs=[];
               $joomla.each($joomla("input[name='articleStr[]']"), function(){
                   articlestrs.push($joomla(this).val());
               });    
               var pricestrs=[];
               $joomla.each($joomla("input[name='priceStr[]']"), function(){
                   pricestrs.push($joomla(this).val());
               });    
               var user="<?php echo $user;?>";
               $joomla('input[name="data_test"]').val($joomla('input[name="invidkStr"]').val()+":"+$joomla('[name="wherhourecStr"]').val()+":"+$joomla('[name="consignidStr"]').val()+":"+$joomla('input[name="txtspecialinsStr"]').val()+":"+$joomla('input[name="shipservtStr"]').val()+":"+$joomla('input[name="paypalinvoice"]').val()+":"+articlestrs+":"+pricestrs+":"+$joomla('input[name="Conveniencefees"]').val()+":"+$joomla('input[name="addSerStr"]').val()+":"+$joomla('input[name="addSerCostStr"]').val()+":"+$joomla('input[name="ratetypeStr"]').val()+":"+$joomla('input[name="companyId"]').val()+":"+$joomla('input[name="insuranceCost"]').val()+":"+$joomla('input[name="extAddSer"]').val()+":"+$joomla("#lengthStr").val()+":"+$joomla("#widthStr").val()+":"+$joomla("#heightStr").val()+":"+$joomla("#weightStr").val()+":"+$joomla("#volStr").val()+":"+$joomla("#volmetStr").val()+":"+$joomla('#totalDecVal').val()+":"+$joomla('input[name=shipmentCost]').val()+":"+$joomla('#couponCodeStr').val()+":"+$joomla('input[name=couponDiscAmt]').val()+":"+user);
               $joomla('input[name="item_name"]').val(''+":"+user);
               $joomla('input[name="item_number"]').val($joomla('input[name="qtyStr"]').val());
               $joomla('input[name="amount"]').val($joomla('input[name="amtStr"]').val());
                
               $joomla('#userprofileFormFive').attr('action',ApiUrl); 
               
               $joomla('#ord_ship #step3 .btn-primary').attr("disabled", false);
               
               
           }else if($joomla(this).val() == 'authorize.net'){
                $joomla(".paypalCreditDebit").hide();
                $joomla(".final_btns").show();
                $joomla(".dvPaymentInformation input").val("");
                $joomla(".dvPaymentInformation select").val("");
                $joomla(".final_ship").removeAttr("onclick");
              
               var articlestrs=[];
               $joomla.each($joomla("input[name='articleStr[]']"), function(){
                   articlestrs.push($joomla(this).val());
               });    
               var pricestrs=[];
               $joomla.each($joomla("input[name='priceStr[]']"), function(){
                   pricestrs.push($joomla(this).val());
               });    
               var user="<?php echo $user;?>";
               $joomla('input[name="item_name"]').val($joomla('input[name="invidkStr"]').val()+":"+$joomla('[name="wherhourecStr"]').val()+":"+$joomla('[name="consignidStr"]').val()+":"+$joomla('input[name="txtspecialinsStr"]').val()+":"+$joomla('input[name="shipservtStr"]').val()+":"+$joomla('input[name="paypalinvoice"]').val()+":"+articlestrs+":"+pricestrs+":"+$joomla('input[name="Conveniencefees"]').val()+":"+$joomla('input[name="addSerStr"]').val()+":"+$joomla('input[name="addSerCostStr"]').val()+":"+$joomla('input[name="ratetypeStr"]').val()+":"+$joomla('input[name="companyId"]').val()+":"+$joomla('input[name="insuranceCost"]').val()+":"+$joomla('input[name="extAddSer"]').val()+":"+$joomla("#lengthStr").val()+":"+$joomla("#widthStr").val()+":"+$joomla("#heightStr").val()+":"+$joomla("#weightStr").val()+":"+$joomla("#volStr").val()+":"+$joomla("#volmetStr").val()+":"+$joomla('#totalDecVal').val()+":"+$joomla('input[name=shipmentCost]').val()+":"+$joomla('#couponCodeStr').val()+":"+$joomla('input[name=couponDiscAmt]').val()+":"+user);
               $joomla('input[name="item_number"]').val($joomla('input[name="qtyStr"]').val());
               $joomla('#shipmethodtotalStr').text($joomla('input[name="shipcostStr"]').val());
               $joomla('input[name="amount"]').val(parseFloat($joomla('#shipmethodtotalStr').html()));
               
                $joomla(".dvPaymentInformation").show();
                $joomla('#userprofileFormFive').attr('action','<?php echo JURI::base(); ?>payment.php');
            }else if($joomla(this).val() == 'Stripe'){
                $joomla(".paypalCreditDebit").hide();
                $joomla(".final_btns").show();
                $joomla(".dvPaymentInformation input").val("");
                $joomla(".dvPaymentInformation select").val("");
                $joomla(".final_ship").removeAttr("onclick");
                  $joomla('input[name=amount]').val($joomla('input[name="shipcostStr"]').val());
                  $joomla('#shipmethodtotalStr').text($joomla('input[name="shipcostStr"]').val());
                  $joomla('#userprofileFormFive').attr('action','');
                  $joomla(".dvPaymentInformation").show();
                }else{
                  $joomla(".paypalCreditDebit").hide();
                  $joomla(".final_btns").show();
                  $joomla(".final_ship").attr("onclick","return onGetCardNonce(event)");
                  $joomla('#userprofileFormFive').attr('action','');        
                  $joomla('#dvPaymentMethod').hide();
                  $joomla(".dvPaymentInformation").hide();
                  $joomla(".prepaid_method_sec").hide();
                  $joomla("input[name='prepaidMethod']").prop("checked",false);
                  $joomla('#shipmethodtotalStr').text($joomla('input[name="shipcostStr"]').val());
                  $joomla('input[name=amtStr]').val($joomla('input[name="shipcostStr"]').val());
                  $joomla('input[name=amount]').val($joomla('input[name="shipcostStr"]').val());
                  
           }
           
           
           if($joomla(this).val() != 'COD'){
            // convenience fees 
               $joomla.ajax({
                   url: "<?php echo JURI::base(); ?>index.php?option=com_userprofile&task=user.get_ajax_data&amount="+$joomla('input[name="shipcostStr"]').val() +"&convflag=1&gateway="+$joomla(this).val()+"&jpath=<?php echo urlencode  (JPATH_SITE); ?>&pseudoParam="+new Date().getTime(),
       			data: { "shippmentid": 1 },
       			dataType:"html",
       			type: "get",
       			beforeSend: function() {
                     //$joomla(".pagshipup_prepaid").show();
                     $joomla(".page_loader").show();
                     $joomla('#ord_ship #step2 .btn-primary').attr("disabled", true);
                  },success: function(data){
                      slot=1
                     
                      var sg=data;
                      sg=sg.split(":");
                      $joomla('input[name="Conveniencefees"]').val(sg[1]);
                      $joomla('input[name=amtStr]').val(sg[0]);
                      $joomla('#shipmethodtotalStr').text(sg[0]);
                       $joomla('input[name=amount]').val(sg[0]);
                       $joomla('input[name="item_name"]').val($joomla('input[name="invidkStr"]').val()+":"+$joomla('[name="wherhourecStr"]').val()+":"+$joomla('[name="consignidStr"]').val()+":"+$joomla('input[name="txtspecialinsStr"]').val()+":"+$joomla('input[name="shipservtStr"]').val()+":"+$joomla('input[name="paypalinvoice"]').val()+":"+articlestrs+":"+pricestrs+":"+$joomla('input[name="Conveniencefees"]').val()+":"+$joomla('input[name="addSerStr"]').val()+":"+$joomla('input[name="addSerCostStr"]').val()+":"+$joomla('input[name="ratetypeStr"]').val()+":"+$joomla('input[name="companyId"]').val()+":"+$joomla('input[name="insuranceCost"]').val()+":"+$joomla('input[name="extAddSer"]').val()+":"+$joomla("#lengthStr").val()+":"+$joomla("#widthStr").val()+":"+$joomla("#heightStr").val()+":"+$joomla("#weightStr").val()+":"+$joomla("#volStr").val()+":"+$joomla("#volmetStr").val()+":"+$joomla('#totalDecVal').val()+":"+$joomla('input[name=shipmentCost]').val()+":"+$joomla('#couponCodeStr').val()+":"+$joomla('input[name=couponDiscAmt]').val()+":"+user);
                       $joomla('input[name="data_test"]').val($joomla('input[name="invidkStr"]').val()+":"+$joomla('[name="wherhourecStr"]').val()+":"+$joomla('[name="consignidStr"]').val()+":"+$joomla('input[name="txtspecialinsStr"]').val()+":"+$joomla('input[name="shipservtStr"]').val()+":"+$joomla('input[name="paypalinvoice"]').val()+":"+articlestrs+":"+pricestrs+":"+$joomla('input[name="Conveniencefees"]').val()+":"+$joomla('input[name="addSerStr"]').val()+":"+$joomla('input[name="addSerCostStr"]').val()+":"+$joomla('input[name="ratetypeStr"]').val()+":"+$joomla('input[name="companyId"]').val()+":"+$joomla('input[name="insuranceCost"]').val()+":"+$joomla('input[name="extAddSer"]').val()+":"+$joomla("#lengthStr").val()+":"+$joomla("#widthStr").val()+":"+$joomla("#heightStr").val()+":"+$joomla("#weightStr").val()+":"+$joomla("#volStr").val()+":"+$joomla("#volmetStr").val()+":"+$joomla('#totalDecVal').val()+":"+$joomla('input[name=shipmentCost]').val()+":"+$joomla('#couponCodeStr').val()+":"+$joomla('input[name=couponDiscAmt]').val()+":"+user);
               
                       $joomla(".page_loader").hide();
                      //$joomla(".pagshipup_prepaid").hide();
                      
                     $joomla('#ord_ship #step2 .btn-primary').attr("disabled", false);
                  }
               });
               
           }  
           
           
           
          
       });
       
   
       $joomla('input[name=ccStr]').click(function(){
          $joomla('.paymentopt').toggle(); 
       });
       
        $joomla(document).on('click','input[name=shipmentStr],#shipmentNewStr',function(){
            //hide / show shipping info
            var shippingInfoStatus = "<?php echo  $shippingInfoStat; ?>";
            //end
           shippingCost = 0;
            var rateType = $joomla("input[name=shipmentNewStr]:checked").val();
            if(rateType != undefined){
                var rateTypeArr = rateType.split(":");
                rateType = rateTypeArr[6];
                shippingCost = rateTypeArr[4];
                $joomla('input[name=shipmentCost]').val(shippingCost);
                
            }
            
            
            
            $joomla('#divShipCOstOne').addClass("rdo_cust shp-addnew1");
            $joomla('#divShipCOstOne').show();
           
           var resv=$joomla('input[name=shipmentStr]').val();
           var whsc=$joomla('#wherhourecStr').val()
           whsc=whsc.split(",");
   
           var shipetype=$joomla('#shipmenttype').val();
           var bustype=$joomla('#bustypeStr').val();
           var bustypeArr = bustype.split(",");
           var bustypeInd = bustypeArr[0];
           
             var dvalue=[];
             var dvalueStr = "";
               // $joomla.each($joomla("#kk_table input[name='priceStr[]'].True"), function(){
               $joomla.each($joomla("#kk_table input[name='priceStr[]']"), function(){
                  //if($joomla(this).attr("data-insurance") == "1"){
                    dvalue.push($joomla(this).val());
                 // }
                 dvalueStr +=$joomla(this).val()+",";
               }); 
               
               var total = 0;
               for (var i = 0; i < dvalue.length; i++) {
                   total = parseFloat(total) + parseFloat(dvalue[i]);
               }
               
               dtotal = total.toFixed(2);
               
               $joomla('#totalDecVal').val(dtotal);
               
               repackLblStr = $joomla('#repackLblStr').val();
               repackLblArr = repackLblStr.split(",");
               
                function myFilter(elm){
                return (elm != null && elm !== false && elm !== "");
                }
               repackLblArr = repackLblArr.filter(myFilter);
               repackLblStr = repackLblArr.toString();
               
               
         
           $joomla.ajax({
   			url: "<?php echo JURI::base(); ?>index.php?option=com_userprofile&task=user.get_ajax_data&paymenttype="+$joomla('input[name=shipmentStr]').val() +"&wherhourec="+ whsc+"&invidk="+$joomla('#invidkStr').val() +"&qty="+$joomla('#qtyStr').val() +"&destination="+$joomla('#dtStr').val() +"&volres="+$joomla('#volresStr').val() +"&tyserv="+$joomla('#tyserviceStr').val() +"&munits="+$joomla('#mrunitsStr').val()+"&source="+$joomla('#srStr').val()+"&shiptype="+shipetype+"&bustype="+bustypeInd+"&dvalue="+dtotal+"&user=<?php echo $user;?>&shippmentflag=1&jpath=<?php echo urlencode  (JPATH_SITE); ?>&pseudoParam="+new Date().getTime(),
   			data: { "shippmentid": $joomla('input[name=shipmentStr]').val(),"length":$joomla("#lengthStr").val(),"width":$joomla("#widthStr").val(),"height":$joomla("#heightStr").val(),"grosswt":$joomla("#weightStr").val(),"volume":$joomla("#volStr").val(),"volumetwt":$joomla("#volmetStr").val(),"dvalueStr":dvalueStr,"shipmentCost":shippingCost,"destCnt":$joomla("#dtStr").val(),"rateType":rateType,"repackLblStr":repackLblStr},
   			dataType:"html",
   			type: "get",
   			beforeSend: function() {
   			     $joomla('#divShipCOstTwo').hide();
                 //$joomla("#divShipCOstOne").html("<img src='/components/com_userprofile/images/loader.gif'>");
                 $joomla('.page_loader').show();
                 $joomla('#ord_ship #step2 .btn-primary').attr("disabled", true);
              },success: function(data){
                  
                  $joomla('#ord_ship #step2 .btn-primary').attr("disabled", false);
                  //$joomla("#divShipCOstOne").html('');
                  $joomla('.page_loader').hide();
                  //$joomla('#divShipCOstTwo').html('');
                  $joomla('#divShipCOstOne').html(data+'<div class="clearfix"></div>');
                                 
                  var hide_shoew_text = "";
                  if(shippingInfoStatus=="ACT"){
                     $joomla("#divShipCOstTwo,.shipping-costtbl,.finalShippingCostInfo").show();

                  }else{
                     $joomla("#divShipCOstTwo,.shipping-costtbl,.finalShippingCostInfo").hide();
                  }

                  if(data==""){
                     $joomla('input[name=shipmentStr]').filter(':radio').prop('checked',false);
                     $joomla('#divShipCOstOne').removeClass("rdo_cust shp-addnew1");
                     $joomla('#divShipCOstTwo').removeClass("rst_text shp-addnew1");
                     alert("No service rates available for destination");                   
                  }
                
   	    	}
   		});
   		
   		
   		 //$joomla('#divShipCOstTwo').show();
         $joomla('#divShipCOstTwo').addClass("rst_text shp-addnew1");
           
           var scosship=$joomla('input[name=shipmentNewStr]:checked').val();
          scosship=scosship.split(":");
      
          $joomla('#shipmethodStrValue').html(scosship[4]);
          $joomla('#shipmethodStrValuetwo').html(scosship[0]);
          var sfixedNum = parseFloat(scosship[0]).toFixed(2);
          $joomla('#shipmethodtotalStr').html(sfixedNum);
          $joomla('input[name=shipcostStr]').val(scosship[0]);
          $joomla('input[name=shipservtStr]').val(scosship[1]);
          $joomla('input[name=amtStr]').val(sfixedNum);
           $joomla('input[name=amount]').val(sfixedNum);
          $joomla("#addserStr").html(scosship[2]);
          
          if(scosship[3]){
              $joomla("#discountStr").html(scosship[3]);
              discountVal = scosship[3];
          }
          else{
              $joomla("#discountStr").html("0.00");
              discountVal = "0.00";
          }
          
          if(scosship[5]){
          $joomla("#insuranceDiv").html(scosship[5]);
          $joomla("input[name='insuranceCost']").val(scosship[5]);
          insCost = scosship[5];
          }
          else{
          $joomla("#insuranceDiv").html("0.00");
          $joomla("input[name='insuranceCost']").val(0.00);
          insCost = "0.00";
          }
          
          if(scosship[8]){
          $joomla("#fuelCharges").html(scosship[8]);
          fuelCharges = scosship[8];
          }
          else{
          $joomla("#fuelCharges").html("0.00");
          fuelCharges = "0.00";
          }
          
          $joomla("#storageCharges").html(scosship[9]);
      	  $joomla('#divShipCOstTwo').html('<table class="table-responsive shipTable"><tr><td><?php echo Jtext::_('COM_USERPROFILE_SHIP_SHIPPING_COST');?></td><td>$'+scosship[4]+'</td></tr><tr><td><?php echo Jtext::_('COM_USERPROFILE_SHIP_FUEL_CHARGES');?> :</td><td>$'+fuelCharges+'</td></tr><tr><td><?php echo Jtext::_('COM_USERPROFILE_SHIP_INSURANCE_COST');?> : </td><td>$'+insCost+'</td></tr><tr><td><?php echo Jtext::_('COM_USERPROFILE_SHIP_ADDITIONAL_SERVICES');?> : </td><td>$<span id="addserStr1">'+scosship[2]+'</span></td></tr><tr><td><?php echo Jtext::_('COM_USERPROFILE_SHIP_POPUP_STORAGE_CHARGES');?> :</td><td>$<span id="storageCharges">'+scosship[9]+'</span></td></tr><tr><td><?php echo Jtext::_('COM_USERPROFILE_SHIP_DISCOUNT');?> : </td><td>$'+discountVal+'</td></tr><tr><td><?php echo Jtext::_('COM_USERPROFILE_SHIP_TOTAL_COST');?> : </td><td>$<span id="totalamountDiv">'+ scosship[0]+'</span></td></tr>');
      	  
      	  //alert($joomla('#divShipCOstTwo').html());
      	  $joomla('input[name=ratetypeStr]').val(scosship[6]);
   		
   		
       });
       
    //     $joomla('#shipmentNewStr').live('click',function(){
            
    //       $joomla('#divShipCOstTwo').show();
    //       $joomla('#divShipCOstTwo').addClass("rst_text shp-addnew1");
           
    //       var scosship=$joomla('input[name=shipmentNewStr]:checked').val();
    //       scosship=scosship.split(":");
    //       $joomla('#shipmethodStrValue').html(scosship[4]);
    //       $joomla('#shipmethodStrValuetwo').html(scosship[0]);
    //       var sfixedNum = parseFloat(scosship[0]).toFixed(2);
    //       $joomla('#shipmethodtotalStr').html(sfixedNum);
    //       $joomla('input[name=shipcostStr]').val(scosship[0]);
    //       $joomla('input[name=shipservtStr]').val(scosship[1]);
    //       $joomla('input[name=amtStr]').val(sfixedNum);
    //       $joomla('input[name=amount]').val(sfixedNum);
    //       $joomla("#addserStr").html(scosship[2]);
          
    //       if(scosship[3]){
    //           $joomla("#discountStr").html(scosship[3]);
    //           discountVal = scosship[3];
    //       }
    //       else{
    //           $joomla("#discountStr").html("0.00");
    //           discountVal = "0.00";
    //       }
          
    //       if(scosship[5]){
    //       $joomla("#insuranceDiv").html(scosship[5]);
    //       $joomla("input[name='insuranceCost']").val(scosship[5]);
    //       insCost = scosship[5];
    //       }
    //       else{
    //       $joomla("#insuranceDiv").html("0.00");
    //       $joomla("input[name='insuranceCost']").val(0.00);
    //       insCost = "0.00";
    //       }
          
    //       if(scosship[8]){
    //       $joomla("#fuelCharges").html(scosship[8]);
    //       fuelCharges = scosship[8];
    //       }
    //       else{
    //       $joomla("#fuelCharges").html("0.00");
    //       fuelCharges = "0.00";
    //       }
          
    //       $joomla("#storageCharges").html(scosship[9]);
    //   	  $joomla('#divShipCOstTwo').html('<table class="table-responsive shipTable"><tr><td><?php echo Jtext::_('COM_USERPROFILE_SHIP_SHIPPING_COST');?></td><td>$'+scosship[4]+'</td></tr><tr><td><?php echo Jtext::_('COM_USERPROFILE_SHIP_FUEL_CHARGES');?> :</td><td>$'+fuelCharges+'</td></tr><tr><td><?php echo Jtext::_('COM_USERPROFILE_SHIP_INSURANCE_COST');?> : </td><td>$'+insCost+'</td></tr><tr><td><?php echo Jtext::_('COM_USERPROFILE_SHIP_ADDITIONAL_SERVICES');?> : </td><td>$<span id="addserStr1">'+scosship[2]+'</span></td></tr><tr><td><?php echo Jtext::_('COM_USERPROFILE_SHIP_POPUP_STORAGE_CHARGES');?> :</td><td>$<span id="storageCharges">'+scosship[9]+'</span></td></tr><tr><td><?php echo Jtext::_('COM_USERPROFILE_SHIP_DISCOUNT');?> : </td><td>$'+discountVal+'</td></tr><tr><td><?php echo Jtext::_('COM_USERPROFILE_SHIP_TOTAL_COST');?> : </td><td>$<span id="totalamountDiv">'+ scosship[0]+'</span></td></tr>');
    //   	  $joomla('input[name=ratetypeStr]').val(scosship[6]);
    //   });
     
       $joomla("#tabs2").on("click","input[name='txtId']",function(){
           $joomla("#promoDiscountStr").html('0.00');
           $joomla('#step1 #j_table').show();
           $joomla('.paygaterrormsg').html("");
           
            var checkboxLen = $joomla("input[name='txtId']").filter(":checked").length;
          if(checkboxLen == 0){
                $joomla("#selectAll").prop("checked",false);
                $joomla('.shipAll').hide();
                $joomla('tr .ship:first-child').show();
          }
          $joomla("#checkboxCount").val(checkboxLen);
           
          var txtIdVal = $joomla(this).val();
           var indVal=txtIdVal.split(":");
           var wtUnits = indVal[17];
           var dimUnits = indVal[12];
           var types = indVal[19];
           var stype = indVal[20];
           var dtype = indVal[21];
           var dhub = indVal[22];
          
           
           $joomla.each($joomla("input[name='txtId']:checked"), function(){
               //$joomla(this).closest('tr').find('input[name="txtQty"]').val($joomla(this).closest('tr').find('input[name="ItemQtyEdit"]').val());
           });
         
            var x=0;
            var xx='';
             
             $joomla('#shipmenttype').val(types);
             $joomla("input[name='txtId']:checked").each( function () {
                 
           var txtIdValLoop = $joomla(this).val();
           var indValLoop=txtIdValLoop.split(":");
           var wtUnitsLoop = indValLoop[17];
           var dimUnitsLoop = indValLoop[12];
           var shipTypeLoop = indValLoop[19];
           var sTypeLoop = indValLoop[20];
           var dTypeLoop = indValLoop[21];
           var dhubLoop = indValLoop[22];
           
         
                 
               if(shipTypeLoop==types)
               {
                   
               }else{
                 x=1;
                 xx="Shipment Type";
               }
               if(sTypeLoop==stype)
               {
                   
               }else{
                 x=2;
                 xx="Source Type";
               }
               if(dTypeLoop==dtype)
               {
                   
               }else{
                 x=3;
                 xx="Destnation Type";
               }
               if(wtUnits == wtUnitsLoop)
               {
                   
               }else{
                 x=4;
                 xx="Weight Units";
               }
               
               if(dimUnits == dimUnitsLoop)
               {
                   
               }else{
                 x=5;
                 xx="Measurement Units";
               }
                if(dhubLoop == dhub)
               {
                  
               }else{
                 x=6;
                 xx="Destnation Hubs";
               }
               
               
             });
             
            
             
             if(x!=0){
               $joomla('#ship_all').modal("show");
               $joomla('.shipAll').hide();
               $joomla('.shipAll').next().addClass("not_same_error");
               $joomla('#confirmq').hide();
               $joomla('#error').html("Please check Shipping "+xx+" are not same");
               $joomla('#error').show();
               $joomla(":checkbox").prop("checked", false);
               $joomla(this).closest('tr').find('input[name="txtId"]').prop('checked',false);
               if($joomla('input[name="shipment"]:checked').val()=="1"){
                   $joomla('#tabs2 #j_table tbody tr#dfg').remove(); 
               }else{
                   $joomla('#tabs2 #u_table tbody tr#dfg').remove(); 
               }    
               $joomla('#step1').hide(); 
               $joomla('#step1 #j_table tbody').remove();    
               //$joomla('#ord_ship #k_table tbody').remove();
               $joomla('#ord_ship #kk_table tbody').remove(); 
               $joomla('#ord_ship #ki_table tbody').remove();    
               $joomla.each($joomla("input[name='txtId']"), function(){ 
                 $joomla(this).closest('tr').find('input[name="txtId"]').prop('checked',false);
                 originalVal = $joomla(this).closest('tr').find('input[name="ItemQtyTxt"]').val();
                   $joomla(this).closest('tr').find('input[name="ItemQtyEdit"]').attr('value',originalVal);
                   $joomla(this).closest('tr').find('input[name="txtQty"]').val(originalVal);
                   $joomla(this).closest('tr').find('td:eq(2)').html(originalVal);
               })
               $joomla("#ord_ship .ship").click(true); 
               $joomla.each($joomla(".wrchild"), function(){ 
                   $joomla('.wrchild').val('+');
               })    
               return false;
             }     
   
              $joomla('#step1 #j_table tbody').remove();    
              //$joomla('#ord_ship #k_table tbody').remove(); 
              $joomla('#ord_ship #kk_table tbody').remove(); 
              $joomla('#ord_ship #ki_table tbody').remove();    
             
              $joomla('#j_table').focus();
              $joomla('#step1').css('display','block');
             
              $joomla('#ord_ship #step3').hide(); 
              $joomla('#ord_ship #step2').hide(); 
   
           var ischecked= $joomla(this).is(':checked');
         if(ischecked){
            $joomla(".check_all_items").each(function(){
               let wr_no = $joomla(this).attr("data-id");
               if(wr_no == indVal[1]){
                  $joomla(this).prop("checked",true);
               }
           });
             
               if($joomla('input[name="shipment"]:checked').val()=="1"){
                   
              
                   var rs=$joomla(this).closest('tr').find('td:eq(1)').text();
                   var htmsw='';
                   $joomla('#tabs2 #j_table tbody tr').each( function () {
                       if($joomla(this).attr('id')==rs){ 
                         htmsw+='<tr id="dfg">'+$joomla(this).html()+'</tr>';
                       }
                   })
                   if($joomla(this).closest('tr').find('.wrchild').val()=='-'){
                       $joomla(this).closest('tr').find('.wrchild').val('-');
                   }
                   else
                   {
                       $joomla.each($joomla('#tabs2 #j_table tbody tr#dfg'), function(){
                           console.log($joomla(this).find('td:eq(1)').text()+"-----"+rs)
                           if($joomla(this).find('td:eq(1)').text()==rs){ 
                              $joomla(this).remove();
                           }
                       });    
                       $joomla(this).closest('tr').after(htmsw);
                       if($joomla(this).closest('tr').find('.wrchild').val()=='+'){
                           $joomla(this).closest('tr').find('.wrchild').val('-');
                       }    
                   }     
                   $joomla.each($joomla('#tabs2 #j_table tbody tr#dfg'), function(){
                      // console.log($joomla(this).find('td:eq(1)').text()+"-----"+rs)
                       if($joomla(this).find('td:eq(1)').text()==rs){ 
                           $joomla(this).closest('tr').find('input[name="txtId"]').prop('checked',true);
                           $joomla(this).closest('tr').find('input[name="txtId"]').prop('readonly',true);
                           $joomla(this).closest('tr').find('input[name="txtId"]').css('pointer-events','none');
                       }else{
                       }    
                   })
           
               }else{
                 
                   
                   var rs=$joomla(this).closest('tr').find('td:eq(1)').text();
                   
                   var htms='';
                   $joomla('#tabs2 #u_table tbody tr').each( function () {
                       if($joomla(this).attr('id')==rs){ 
                         htms+='<tr id="dfg">'+$joomla(this).html()+'</tr>';
                       }
                   })
                   if($joomla(this).closest('tr').find('.wrchild').val()=='-'){
                       $joomla(this).closest('tr').find('.wrchild').val('-');
                   }else
                   {
                       $joomla.each($joomla('#tabs2 #u_table tbody tr#dfg'), function(){
                           console.log($joomla(this).find('td:eq(1)').text()+"-----"+rs)
                           if($joomla(this).find('td:eq(1)').text()==rs){ 
                              $joomla(this).remove();
                           }
                       });    
                       $joomla(this).closest('tr').after(htms);
                       if($joomla(this).closest('tr').find('.wrchild').val()=='+'){
                           $joomla(this).closest('tr').find('.wrchild').val('-');
                       }    
                   }     
              
                 $joomla.each($joomla('#tabs2 #u_table tbody tr#dfg'), function(){
                       if($joomla(this).find('td:eq(1)').text()==rs){ 
                           $joomla(this).closest('tr').find('input[name="txtId"]').prop('checked',true);
                           $joomla(this).closest('tr').find('input[name="txtId"]').prop('readonly',true);
                           $joomla(this).closest('tr').find('input[name="txtId"]').css('pointer-events','none');
                       }else{
                       }    
                 })
             
               }
        
    
         }else{
             
             
           
               if($joomla('input[name="shipment"]:checked').val()=="1"){
         
                   var rs=$joomla(this).closest('tr').find('td:eq(1)').text();
                  
                  $joomla.each($joomla('#tabs2 #j_table tbody tr#dfg'), function(){
                      if($joomla(this).closest('tr').find('td:eq(1)').text()==rs){ 
                         $joomla(this).closest('tr').remove();
                       }
                   })
                   if($joomla(this).closest('tr').find('.wrchild').val()=='-'){
                       $joomla(this).closest('tr').find('.wrchild').val('+');
                   } 
       
                   }else{
         
                   var rs=$joomla(this).closest('tr').find('td:eq(1)').text();
                  
                  $joomla.each($joomla('#tabs2 #u_table tbody tr#dfg'), function(){
                      if($joomla(this).closest('tr').find('td:eq(1)').text()==rs){ 
                         $joomla(this).closest('tr').remove();
                       }
                   })
                   if($joomla(this).closest('tr').find('.wrchild').val()=='-'){
                       $joomla(this).closest('tr').find('.wrchild').val('+');
                   } 
                   
               }
   
         }       
             
   
             if($joomla('#step1 #j_table tr').length>0){
                var tds='<tr>';
                var tdns='<tr>';
                var tdns2='<tr>';
                var whs=[];
                var idksn=[];
                var qtyl=[];
                var volres=[];
                var tyservice=[];
                var sr=[];
                var dt=[];
                var mrunits=[];
                var srhub=[];
                var ht=[];
                var wd=[];
                var lg=[];
                var wt=[];
                var dtunit=[];
                var bustype=[];
                var whtr='';
                var j=0;
                var sno=1;
                var vol=[];
                var volmetwt=[];
                var rplables=[];

                $joomla.each($joomla("input[name='txtId']:checked"), function(){
                   if($joomla(this).val()){
                   var loops=$joomla(this).val();
                   //console.log(loops);
                   var loop=loops.split(":");
                   for(i=0;i<loop.length;i++){
                       //if(loop[i]=="" || loop[i]==null){
                        //tds+="";   
                       //}
                       //else{
                        if(i==0){
                           tds+="<td>"+loop[i]+"</td>";
                           tdns+='<td><input type="text"  id="'+j+'" readonly  name="articleStr[]" class="form-control" value="'+loop[i]+'"></td>';
                           tdns2+='<td><input type="text"  id="'+j+'" readonly name="article2Str[]" value="'+loop[i]+'"></td>';
                         }    
                        if(i==1){
                           //$joomla('#wherhourecStr').val(loop[i]); 
                           whs.push(loop[i]);
                           tds+="<td class='warehouseStr'>"+loop[i]+"</td>";
                        }    
                        if(i==2){
                          qtyl.push($joomla(this).closest('tr').find('input[name="ItemQtyEdit"]').attr('value'));
                          tds+="<td>"+$joomla(this).closest('tr').find('input[name="ItemQtyEdit"]').attr('value')+"</td>";
                          tdns+="<td>"+$joomla(this).closest('tr').find('input[name="ItemQtyEdit"]').attr('value')+"</td>";
                          tdns2+="<td>"+$joomla(this).closest('tr').find('input[name="txtQty"]').attr('value')+"</td>";
                        }    
                        if(i==3){
                           $joomla('#trackingidStr').val(loop[i]);
                           tds+="<td>"+loop[i]+"</td>";
                           tdns+="<td>"+loop[i]+"</td>";
                           tdns2+="<td>"+loop[i]+"</td>";
                        }
                        if(i==4){
                           idksn.push(loop[i]); 
                           whtr=loop[i];
                        }
                        if(i==5){
                           $joomla('#ItemPriceStr').val(loop[i]);
                           //tdns+="<td>"+loop[i]+"</td>";
                            var quantity = $joomla(this).closest('tr').find('input[name="ItemQtyEdit"]').attr('value');
                           var totalCost = quantity*loop[24];
                           tdns+='<td><input type="text" class="form-control pricechange '+ loop[19] +'"  data-insurance="'+loop[23]+'"  name="priceStr[]"  id="'+j+i+'"  value="'+totalCost.toFixed(2)+'"></td>';
                           tdns2+='<td><input type="text" name="price2Str[]"  id="'+j+i+'" readonly  value="'+loop[i]+'"></td>';
                        }
                        if(i==6){
                           $joomla('#costStr').val(loop[i]);
                           //tdns+="<td>"+loop[i]+"</td>";
                       }
                        if(i==8){
                           volres.push(loop[i]);
                        }
                        if(i==9){
                           tyservice.push(loop[i]);
                           //console.log(loop[i])
                        }
                        if(i==10){
                            
                           sr.push(loop[i]);
                           //console.log(loop[i])
                        }
                        if(i==11){
                           dt.push(loop[i]);
                           //console.log(loop[i])
                        }
                        if(i==12){
                           mrunits.push(loop[i]);
                           //console.log(loop[i])
                        }
                        //if(i==13){
                           //srhub.push(loop[i]);
                           //console.log(loop[i])
                        //}
                        if(i==13){
                           lg.push(loop[i]);
                        }
                        if(i==14){
                           wd.push(loop[i]);
                        }
                        if(i==15){
                           ht.push(loop[i]);
                        }
                        if(i==16){
                           wt.push(loop[i]);
                        }
                        if(i==17){
                           dtunit.push(loop[i]);
                        }
                        if(i==28){
                           bustype.push(loop[i]);
                        }
                        if(i==18){
                         var fileName = loop[i];
                         console.log("fileName:"+fileName);
                         var ext = fileName.substring(fileName.lastIndexOf('.') + 1);
                         if(ext =="GIF" || ext=="gif" || ext =="jpeg" || ext=="JPEG"  || ext=="pdf"  || ext =="PNG" || ext=="png"  || ext=="JPG"  || ext=="jpg" ){
                           var image1=image2=image3 ="";
                           var hrefs=loop[i];
                           hrefs=hrefs.split(' ').join('%20');
                           hrefs=hrefs.replace("#",":");
                           
                           var hrefs1=loop[25];
                           hrefs1=hrefs1.split(' ').join('%20');
                           hrefs1=hrefs1.replace("#",":");
                           if(hrefs1 !='')
                           image1='<a class="sfile" href="'+hrefs1+'" target="_blank">(<?php echo Jtext::_('COM_USERPROFILE_INVOICE');?>)</a>';
                           
                           var hrefs2=loop[26];
                           hrefs2=hrefs2.split(' ').join('%20');
                           hrefs2=hrefs2.replace("#",":");
                           if(hrefs2 !='')
                           image2='<a class="sfile" href="'+hrefs2+'" target="_blank">(<?php echo Jtext::_('COM_USERPROFILE_INVOICE');?>)</a>';
                           
                           var hrefs3=loop[27];
                           hrefs3=hrefs3.split(' ').join('%20');
                           hrefs3=hrefs3.replace("#",":");
                           if(hrefs3 !='')
                           image3='<a class="sfile" href="'+hrefs3+'" target="_blank">(<?php echo Jtext::_('COM_USERPROFILE_INVOICE');?>)</a>';
                           
                           
                           
                           tdns+='<td> <div><div><label><?php echo Jtext::_('COM_USERPROFILE_SHIP_POPUP_INVOICE_UPLOAD');?></label></div><div><input type="file" class="upoadfe" name="invFile[]" multiple id='+whtr+'></div><div><p><?php echo Jtext::_('COM_USERPROFILE_SHIP_KTABLE_UPLOAD_EXT_TYPE_TXT');?></p></div></div><div class="clearfix"></div><a class="sfile" href="'+hrefs+'" target="_blank">(<?php echo Jtext::_('COM_USERPROFILE_INVOICE');?>)</a>'+image1+image2+image3+'</td>';
                         }else{
                           tdns+='<td> <div><div><label><?php echo Jtext::_('COM_USERPROFILE_SHIP_POPUP_INVOICE_UPLOAD');?></label></div><div><input type="file" class="upoadfe" name="invFile[]" multiple id='+whtr+'></div><div><p><?php echo Jtext::_('COM_USERPROFILE_SHIP_KTABLE_UPLOAD_EXT_TYPE_TXT');?></p></div></div></td>';
                         }
   
                         var file2Name = loop[i];
                         console.log("file2Name:"+file2Name);
                         var ext = file2Name.substring(file2Name.lastIndexOf('.') + 1);
                         if(ext =="GIF" || ext=="gif" || ext =="jpeg" || ext=="JPEG"  || ext=="pdf"  || ext =="PNG" || ext=="png"  || ext=="JPG"  || ext=="jpg" ){
                           var hrefs2=loop[i];
                           hrefs2=hrefs2.split(' ').join('%20');
                           hrefs2=hrefs2.replace("#",":");
                           tdns2+='<td> <div><div><label><?php echo Jtext::_('COM_USERPROFILE_SHIP_POPUP_INVOICE_UPLOAD');?></label></div><div><input type="file" class="upoadfe2" name="inv2File[]" multiple id='+whtr+'></div><div><p><?php echo Jtext::_('COM_USERPROFILE_SHIP_KTABLE_UPLOAD_EXT_TYPE_TXT');?></p></div></div><div class="clearfix"></div><a class="sfile" href="'+hrefs2+'" target="_blank">(<?php echo Jtext::_('COM_USERPROFILE_INVOICE');?>)</a></td>';
                         }else{
                           tdns2+='<td> <div><div><label><?php echo Jtext::_('COM_USERPROFILE_SHIP_POPUP_INVOICE_UPLOAD');?></label></div><div><input type="file" class="upoadfe2" name="inv2File[]" multiple id='+whtr+'></div><div><p><?php echo Jtext::_('COM_USERPROFILE_SHIP_KTABLE_UPLOAD_EXT_TYPE_TXT');?></p></div></div></td>';
                         }
   
                        }
                        if(i==29){
                           vol.push(loop[i]);
                        }
                        if(i==30){
                           volmetwt.push(loop[i]);
                        }
                        if(i==31){
                           rplables.push(loop[i]);
                        }
                        
       
                       j++;
                      //}
                 }
                 whs.join(", ");
                 idksn.join(", ");
                 volres.join(", ");
                 tyservice.join(", ");
                 sr.join(", ");
                 dt.join(", ");
                 mrunits.join(", ");
                 srhub.join(", ");
                 bustype.join(", ");
                 vol.join(", ");
                 volmetwt.join(", ");
                 rplables.join(", ");
   
                 lg.join(", ");
                 wd.join(", ");
                 ht.join(", ");
                 wt.join(", ");
                 dtunit.join(", ");
                 
                 qtyl.join(", ");
                 $joomla('#qtyStr').val(qtyl);
                 $joomla('#qty2Str').val(qtyl);
                 
                 $joomla('#wherhourecStr').val(whs);
                 $joomla('#wherhourec2Str').val(whs);
                 $joomla('#invidkStr').val(idksn);
                 $joomla('#invidk2Str').val(idksn);
                 $joomla('#volresStr').val(volres);
                 $joomla('#tyserviceStr').val(tyservice);
                 $joomla('#mrunitsStr').val(mrunits);
                 $joomla('#volStr').val(vol);
                 $joomla('#volmetStr').val(volmetwt);
                 $joomla('#repackLblStr').val(rplables);
                 
                 
                 $joomla('#lengthStr').val(lg);
                 $joomla('#widthStr').val(wd);
                 $joomla('#heightStr').val(ht);
                 $joomla('#weightStr').val(wt);
                 $joomla('#dtunitStr').val(dtunit);
                 $joomla('#bustypeStr').val(bustype);
                 
                  
                 $joomla('#srhub').val(srhub);
                 $joomla('#srStr').val(sr);
                 $joomla('#dtStr').val(dt);
                 tds+='<td><span data-sno="'+loop[7]+'" class="discardShip"></span></td>';
                // tds+='<td style="display:none;">'+sno+'</span></td>';
                 tds+="</tr>";
                 tdns+="</tr>";
                 tdns2+="</tr>";
                 
                }
                sno++;
               });
       
               $joomla('#step1 #j_table:first').append(tds);
               //$joomla('#ord_ship #k_table:last').append(tdns);
               $joomla('#ord_ship #kk_table:last').append(tdns);
               $joomla('#ord_ship #ki_table:last').append(tdns2);
               $joomla('input[name=shipmentStr]').filter(':radio').prop('checked',false);
               $joomla('input[name=cc]').filter(':radio').prop('checked',false);
               $joomla('#divShipCOstOne').html('');
               $joomla('#ChangeShippingAddressNew').html();
               
               var sdf=$joomla('#txtbiladdress').attr("data-address");
               var sdatas=sdf.replace(/,/g,"<br>");
               $joomla('#ChangeShippingAddressNew').html(sdatas); 
               $joomla('#toaddressTxt').val(sdf);         
               $joomla('#destingaddreesStr').val(sdatas); 
               
               totalQnt = $joomla(this).closest('tr').find('input[name="txtQty"]').attr('value')
               shipQnt = $joomla(this).closest('tr').find('input[name="ItemQtyEdit"]').attr('value');
               finalQnt =  totalQnt-shipQnt;
                //$joomla(this).closest('tr').find('td:eq(5)').html(finalQnt);
                $joomla(this).closest('tr').find('td:eq(6) input[name="txtQty"]').val(finalQnt);
               
             }
             
             if(!ischecked){
                 
               originalVal = $joomla(this).closest('tr').find('input[name="ItemQtyTxt"]').val();
               $joomla(this).closest('tr').find('input[name="ItemQtyEdit"]').attr('value',originalVal);
               $joomla(this).closest('tr').find('input[name="txtQty"]').val(originalVal);
               $joomla(this).closest('tr').find('td:eq(2)').html(originalVal);
               
               
             }
             
             if($joomla('#step1 #j_table tbody tr td').length==0){
                 $joomla('.btn-danger').click();
             }
           $joomla("#srol").focus();  
           $joomla(".selcetedCount").html(parseInt($joomla(".check_all_items:checked").length) + parseInt($joomla(".check_all_items_repack:checked").length ));
       });     
   
       
   
       $joomla('#tabs2').on('click','.ship',function(){
           
            // $joomla('#step1').show();
            // $joomla('#step1 #j_table').show();
           
           var txtIdVal = $joomla(this).prev().val();
           var indVal=txtIdVal.split(":");
           var wtUnits = indVal[17];
           var dimUnits = indVal[12];
           var types = indVal[19];
           var stype = indVal[20];
           var dtype = indVal[21];
           var dhub = indVal[22];
         
         var x=0;
         var xx='';
         
      
         $joomla('#shipmenttype').val(types);
         
         // check for the same shipment type, source type, destination type, weight and dimentions
         
         $joomla("input[name='txtId']:checked").each( function () {
             
             
             
                           var txtIdValLoop = $joomla(this).val();
                           var indValLoop=txtIdValLoop.split(":");
                           var wtUnitsLoop = indValLoop[17];
                           var dimUnitsLoop = indValLoop[12];
                           var shipTypeLoop = indValLoop[19];
                           var sTypeLoop = indValLoop[20];
                           var dTypeLoop = indValLoop[21];
                           var dhubLoop = indValLoop[22];
                             
                           if(shipTypeLoop==types)
                           {
                               
                           }else{
                             x=1;
                             xx="Shipment Type";
                           }
                           if(sTypeLoop==stype)
                           {
                               
                           }else{
                             x=2;
                             xx="Source Type";
                           }
                           if(dTypeLoop==dtype)
                           {
                               
                           }else{
                             x=3;
                             xx="Destnation Type";
                           }
                          if(wtUnits == wtUnitsLoop)
                           {
                               
                           }else{
                             x=4;
                             xx="Weight Units";
                           }
                           
                           if(dimUnits == dimUnitsLoop)
                           {
                               
                           }else{
                             x=5;
                             xx="Measurement Units";
                           }
                           if(dhubLoop==dhub)
                           {
                               
                           }else{
                             x=6;
                             xx="Destnation Hubs";
                           }
               
         });
         
         //end
         
         if(x!=0){
           $joomla('#ship_all').modal("show");
            $joomla('.shipAll').hide();
            $joomla('.shipAll').next().addClass("not_same_error");
           $joomla('#confirmq').hide();
           $joomla('#error').html("Please check Shipping "+xx+" are not same");
           $joomla('#error').show();
           $joomla(":checkbox").prop("checked", false);
           if($joomla('input[name="shipment"]:checked').val()=="1"){
               $joomla('#tabs2 #j_table tbody tr#dfg').remove(); 
           }else{
               $joomla('#tabs2 #u_table tbody tr#dfg').remove(); 
           }
           $joomla('#step1').hide(); 
           $joomla('#step1 #j_table tbody').remove();    
           $joomla('#ord_ship #k_table tbody').remove();    
           $joomla('#ord_ship #ki_table tbody').remove();    
           $joomla.each($joomla("input[name='txtId']"), function(){ 
             $joomla(this).closest('tr').find('input[name="txtId"]').prop('checked',false);
             originalVal = $joomla(this).closest('tr').find('input[name="ItemQtyTxt"]').val();
                   $joomla(this).closest('tr').find('input[name="ItemQtyEdit"]').attr('value',originalVal);
                   $joomla(this).closest('tr').find('input[name="txtQty"]').val(originalVal);
                   $joomla(this).closest('tr').find('td:eq(2)').html(originalVal);
           })
           $joomla("#ord_ship .ship").click(true);  
           $joomla.each($joomla(".wrchild"), function(){ 
               $joomla('.wrchild').val('+');
           })    
           return false;
         }
         
         $joomla(this).closest('tr').find('input[name="txtId"]').click();
         
        
   
       });
   
   
       //ship_img
    //   $joomla('#tabs2').on('click','.ship_img',function(){
    //       var res=$joomla(this).data('id');
    //       $joomla('#viewImage').html('');
    //       if(res.match(/(?:pdf)$/)=="pdf"|| res.match(/(?:pdf)$/)=="PDF") {
    //          $joomla(this).attr('href',res);
    //          $joomla(this).attr('target','-blank');
    //       }else if (res.match(/(?:gif|jpg|png|jpeg|GIF|JPG|PNG|JPEG)$/)) {
    //          $joomla('#viewImage').html("<img src='"+res+"' width='100%'>");
    //       }
    //       else{
    //          $joomla('#viewImage').html("There is no image upload for this order");
    //       }
    //   });
       
       
        $joomla(document).on('click','#tabs2 .ship_img',function(){
            // alert($joomla(this).attr("data-idk"));
         $joomla('#update_invoice_submit').attr('data-idk',$joomla(this).attr("data-idk"));
        $joomla('#viewImage #multimages').html('');
        var expandImg = document.getElementById("expandedImg");
        expandImg.parentElement.style.display = "none";
        var res=$joomla(this).data('id');
        
        var imgarr = res.split("##");
        var imgstr = '';
        
        for(var i=0;i<imgarr.length;i++){
            if (imgarr[i] !=''){ 
                imgext = imgarr[i].split(".");
                if (imgext[imgext.length - 1] != 'pdf') {
                    imgstr += "<div class='col-img-all images-view'><img src='"+imgarr[i]+"' width='100%'></div>";
                }else{
                    imgstr += "<div class='col-img-all pdf-view'><a href='"+imgarr[i]+"' target='_blank' class='pdf-img'><img src='<?php echo JURI::base(); ?>components/com_userprofile/images/pdf-icon.png' width='100%'><span class='btn btn-primary'>View PDF</span></a></div>";
                 }
            }
        }
        if(imgstr !=''){
            $joomla('#viewImage #multimages').html(imgstr);
            var firstImgSrc = $joomla('#multimages .images-view:first img').attr("src");
            if(typeof firstImgSrc !== 'undefined'){
             expandImg.src = $joomla('#multimages .images-view:first img').attr("src");
             expandImg.parentElement.style.display = "block";
            }else{
                expandImg.parentElement.style.display = "none";
            }
            $joomla('#multimages .images-view:first').addClass("img-active");
        }else{
            $joomla('#viewImage #multimages').html("There is no image for this order");
        }
    });
       
       
        $joomla(document).on('click','.col-img-all img',function(){
            $joomla('.col-img-all').removeClass("img-active");
            $joomla(this).parent().addClass("img-active");
            var imgsrc = $joomla(this).attr("src");
            var alttext = $joomla(this).attr("alt");
            var expandImg = document.getElementById("expandedImg");
            var imgText = document.getElementById("imgalttext");
            expandImg.src = imgsrc;
            //imgText.innerHTML = alttext;
            expandImg.parentElement.style.display = "block";
    });
    
       
       $joomla('select[name=txtHistoryStatus]').change(function(){
           var resk=$joomla(this).val();
           if(resk=='ALL'){
               $joomla('.input-sm').val('');
               $joomla('.input-sm').trigger('keyup');
           }else    
           $joomla('.input-sm').val($joomla(this).val());
           $joomla('.input-sm').trigger('keyup');
       });     
       
    //   $joomla(function() {
    
           
    //       // Initialize form validation on the registration form.
    //       // It has the name attribute "registration"
    //       $joomla("form[name='userprofileFormTwo']").validate({
           
    //       // Specify validation rules
    //       rules: {
    //          // The key name on the left side is the name attribute
    //          // of an input field. Validation rules are defined
    //          // on the right side
    //          txtBackCompany: {
    //           required: true
    //         },
    //          txtReturnAddress: {
    //           required: true
    //         },
    //          txtReturnCarrier: {
    //           required: true
    //         },
    //          txtReturnReason: {
    //           required: true
    //         },
    //          txtOriginalOrderNumber: {
    //           required: true
    //         },
    //          txtMerchantNumber: {
    //           required: true
    //         },
    //          txtSpecialInstructions: {
    //           required: true
    //         }
    //       },
    //       // Specify validation error messages
    //       messages: {
    //          txtBackCompany: "<?#php echo Jtext::_('COM_USERPROFILE_RETURN_BACK_COMPANY_VALID')  ?>",
    //          txtReturnAddress: "<?#php echo Jtext::_('COM_USERPROFILE_RETURN_RETURN_ADDRESS_VALID')  ?>",
    //          txtReturnCarrier: "<?#php echo Jtext::_('COM_USERPROFILE_RETURN_RETURN_SHIPPING_CARRIER_VALID')  ?>",
    //          txtReturnReason: "<?#php echo Jtext::_('COM_USERPROFILE_RETURN_RETURN_REASON_VALID')  ?>",
    //          txtOriginalOrderNumber: "<?#php echo Jtext::_('COM_USERPROFILE_RETURN_RETURN_ORDER_NUMBER_VALID')  ?>",
    //          txtMerchantNumber: "<?#php echo Jtext::_('COM_USERPROFILE_RETURN_RETURN_MERCHANT_NUMBER_VALID')  ?>",
    //          txtSpecialInstructions: "<?#php echo Jtext::_('COM_USERPROFILE_RETURN_RETURN_SPECIAL_INSTRUCTION_VALID')  ?>"
   
    //       },
    //       // Make sure the form is submitted to the destination defined
    //       // in the "action" attribute of the form when valid
    //       submitHandler: function(form) {
    //       		// Returns successful data submission message when the entered information is stored in database.
    //   		/*$.post("http://boxon.justfordemo.biz/index.php/register", {
    //   			name1: name,
    //   			email1: email,
    //   			task: register,
    //   			id:  0
    //   		}, function(data) {
    //   			$joomla("#returnmessage").append(data); // Append returned message to message paragraph.
    //   			if (data == "Your Query has been received, We will contact you soon.") {
    //   				$joomla("#registerFormOne")[0].reset(); // To reset form fields on success.
    //   			}
    //   		});*/
    //           if($joomla("input[name=fluReturnShippingLabel]").val()!=""){
    //               $joomla("input[name=fluReturnShippingLabel] #errorTxt-error").html('');
    //               var ext = $joomla('input[name=fluReturnShippingLabel]').val().split('.').pop().toLowerCase();
    //               if($joomla.inArray(ext, ['gif','png','jpg','jpeg','pdf','GIF','PNG','JPG','JPEG','PDF']) == -1) {
    //                   $joomla('input[name=fluReturnShippingLabel]').after('<label id="errorTxt-error" class="error" for="errorTxt">Please Upload extension type png,jpg,jpeg,gif,pdf !</label>');
    //                   return false;
    //               }else{
    //                  $joomla("input[name=fluReturnShippingLabel] #errorTxt-error").html('');    
    //               }
    //           }
    //           $joomla('#ord_return .modal-body').hide();
    //           $joomla('#ord_return .modal-body2').html('<img src="<?#php echo JURI::base(); ?>/components/com_userprofile/images/loader.gif">');
    //          form.submit();
    //       }
    //       });    
    //   });
       
    //     $joomla(function() {
    
           
    //       // Initialize form validation on the registration form.
    //       // It has the name attribute "registration"
    //       $joomla("form[name='userprofileFormThree']").validate({
           
    //       // Specify validation rules
    //       rules: {
    //          // The key name on the left side is the name attribute
    //          // of an input field. Validation rules are defined
    //          // on the right side
    //          txtReturnReason: {
    //           required: true
    //         }
    //       },
    //       // Specify validation error messages
    //       messages: {
    //          txtReturnReason: "Please enter Reason for Hold"
             
    //       },
    //       // Make sure the form is submitted to the destination defined
    //       // in the "action" attribute of the form when valid
    //       submitHandler: function(form) {
    //       		// Returns successful data submission message when the entered information is stored in database.
    //   		/*$.post("http://boxon.justfordemo.biz/index.php/register", {
    //   			name1: name,
    //   			email1: email,
    //   			task: register,
    //   			id:  0
    //   		}, function(data) {
    //   			$joomla("#returnmessage").append(data); // Append returned message to message paragraph.
    //   			if (data == "Your Query has been received, We will contact you soon.") {
    //   				$joomla("#registerFormOne")[0].reset(); // To reset form fields on success.
    //   			}
    //   		});*/
    //           $joomla('#ord_keep .modal-body').hide();
    //           $joomla('#ord_keep .modal-body2').html('<img src="<?#php echo JURI::base(); ?>/components/com_userprofile/images/loader.gif">');
    //          form.submit();
    //       }
    //       });    
    //   });
       
       
       
       
       
    //   $joomla(function() {
    
           
    //       // Initialize form validation on the registration form.
    //       // It has the name attribute "registration"
    //       $joomla("form[name='userprofileFormFour']").validate({
           
    //       // Specify validation rules
    //       rules: {
    //          // The key name on the left side is the name attribute
    //          // of an input field. Validation rules are defined
    //          // on the right side
    //          txtReturnReason: {
    //           required: true
    //         }
    //       },
    //       // Specify validation error messages
    //       messages: {
    //          txtReturnReason: "Please enter Return Reason"
             
    //       },
    //       // Make sure the form is submitted to the destination defined
    //       // in the "action" attribute of the form when valid
    //       submitHandler: function(form) {
    //       		// Returns successful data submission message when the entered information is stored in database.
    //   		/*$.post("http://boxon.justfordemo.biz/index.php/register", {
    //   			name1: name,
    //   			email1: email,
    //   			task: register,
    //   			id:  0
    //   		}, function(data) {
    //   			$joomla("#returnmessage").append(data); // Append returned message to message paragraph.
    //   			if (data == "Your Query has been received, We will contact you soon.") {
    //   				$joomla("#registerFormOne")[0].reset(); // To reset form fields on success.
    //   			}
    //   		});*/
    //          form.submit();
    //       }
    //       });    
    //   });
       
       
      $joomla("input[name='priceStr[]']").live('keypress',function (e) {
       if(e.which == 46){
           if($joomla(this).val().indexOf('.') != -1) {
               return false;
           }
       }
       if (e.which != 8 && e.which != 0 && e.which != 46 && (e.which < 48 || e.which > 57)) {
           return false;
       }
       });
   
       $joomla.validator.addMethod("currentdates", function(value, element) {
        if($joomla('select[name="YearDropDownListStr"]').val()=="<?php echo date('Y');?>" && $joomla('select[name="MonthDropDownListStr"]').val()<="<?php echo date('m');?>"){
         return false 
        }else{
         return true 
        }
       }, '<?php echo Jtext::_('Card expiry year and month not validated');?>');
   
       $joomla("input[name='btnReset']").click(function(e){
          var alt=confirm("Please confirm to Reset the form");
          if(alt==true)    
          $joomla("#userprofileFormOne").trigger("reset");    
       }); 
   
      $joomla("input[name='txtQty']").keyup(function(e){
       if(parseFloat($joomla(this).val())>parseFloat($joomla(this).closest("tr").find("input[name='txtItemQty']").val())){
         $joomla('#myAlertModal').modal("show");
         $joomla('#error').html("Please enter less than quantity Number");
         this.value = this.value.replace($joomla(this).val(), $joomla(this).closest("tr").find("input[name='txtItemQty']").val());
       }else{
         //this.value = this.value.replace(/[^0-9/.]/g, '');
       }
           
       });
       $joomla('.upoadfe').live('change',function(){
           $joomla('input[name=cc]').filter(':radio').prop('checked',false);	
           var ext = $joomla(this).val().split('.').pop().toLowerCase();
           var file_size = $joomla(this)[0].files[0].size;
       	if($joomla.inArray(ext, ['gif','png','jpg','jpeg','pdf']) == -1) {
               alert('Invad Extension!');
               $joomla(this).val('');
           }
           else if(file_size>2097152) {
       		alert("File size is greater than 2MB");
               $joomla(this).val('');
       	}
   	});
       $joomla('.upoadfe2').live('change',function(){
           var ext = $joomla(this).val().split('.').pop().toLowerCase();
           var file_size = $joomla(this)[0].files[0].size;
       	if($joomla.inArray(ext, ['gif','png','jpg','jpeg','pdf']) == -1) {
               alert('Invad Extension!');
               $joomla(this).val('');
           }
           else if(file_size>2097152) {
       		alert("File size is greater than 2MB");
               $joomla(this).val('');
       	}
   	});
    
   
   	$joomla('#country2Txt').on('change',function(){
   	    $joomla('#country2Txt-error').html('');
   	    $joomla('input[name="city2Txt"]').val('');
   		$joomla('#state2Txt').val('');
   		$joomla('#city2Txt').html('');
   		var countryID = $joomla(this).val();
   		if(countryID){
   			$joomla.ajax({
   				url: "<?php echo JURI::base(); ?>index.php?option=com_userprofile&task=user.get_ajax_data&countryid="+$joomla("#country2Txt").val() +"&stateflag=1&jpath=<?php echo urlencode  (JPATH_SITE); ?>&pseudoParam="+new Date().getTime(),
   				data: { "country": $joomla("#countryTxt").val() },
   				dataType:"html",
   				type: "get",
       			beforeSend: function() {
                       //$joomla(".pagshipup").show();
                       $joomla(".page_loader").show();
                       $joomla('.pagshipdown').hide();
                   },    
   				success: function(data){
                       //$joomla(".pagshipup").hide();
                       $joomla(".page_loader").hide();
                       $joomla('.pagshipdown').show();
   					$joomla('#state2Txt').html(data);
   					//$joomla('#city2Txt').html('<option value="">Select City</option>'); 
   				}
   			});
   		}
   		$joomla('#state2Txt').html('<option value="">Select State</option>');
   		//$joomla('#city2Txt').html('<option value="">Select City</option>'); 
   		$joomla('#zip2Txt').val(''); 
   	});
   
   	$joomla('#state2Txt').on('change',function(){
   	    $joomla('#state2Txt-error').html('');
   		$joomla('input[name="city2Txt"]').val('');
   		$joomla('#city2Txt').html('');
   		var stateID = $joomla(this).val();
   		if(stateID){
   			$joomla.ajax({
   				url: "<?php echo JURI::base(); ?>index.php?option=com_userprofile&task=user.get_ajax_data&stateid="+$joomla("#state2Txt").val() +"&cityflag=1&jpath=<?php echo urlencode  (JPATH_SITE); ?>&pseudoParam="+new Date().getTime(),
   				data: { "state": $joomla("#countryTxt").val() },
   				dataType:"html",
   				type: "get",
       			beforeSend: function() {
                       //$joomla(".pagshipup").show();
                       $joomla(".page_loader").show();
                       $joomla('.pagshipdown').hide();
                   },    
   				success: function(data){
                       //$joomla(".pagshipup").hide();
                       $joomla(".page_loader").hide();
                       $joomla('.pagshipdown').show();
   					$joomla('#city2Txt').append(data);
   				}
   			}); 
   		}else{
   			$joomla('#city2Txt').html('<option value="">Select City</option>'); 
   		}
   	});  
   // 	$joomla("input[name='city2Txt']").blur(function(){
           
   //         var val = $joomla(this).val()
   //         var xyz = $joomla('#city2Txt option').filter(function() {
   //             return this.value == val;
   //         }).data('xyz');
   //         if(xyz){
   //             $joomla(this).val(val);
   //         }
   //         $joomla("input[name='city2Txtdiv']").val(xyz);
   //      });
   
   	
   	$joomla('#addusers').on('click',function(){
   	    $joomla('#userprofileFormSeven')[0].reset();
   	    $joomla('#divShipCOstOne').html('');
   	    $joomla('input[name=shipmentStr]').filter(':radio').prop('checked',false);	
   	    $joomla.ajax({
   	        url: "<?php echo JURI::base(); ?>index.php?option=com_userprofile&task=user.get_ajax_data&getadduserconsigflag=1&jpath=<?php echo urlencode  (JPATH_SITE); ?>&pseudoParam="+new Date().getTime(),
   			data: { "useridtypes":1},
   			dataType:"html",
   			type: "get",
   			success: function(data){
   			  var dsed=data;
   			  dsed=dsed.split(":");  
   			  console.log("consig data:"+data);
   			  $joomla("input[name='typeuserTxt']").val(dsed[0]);
   			  $joomla("input[name='idTxt']").val(dsed[1]);
   			}
   		}); 
   	});
   	
   	// getcouponcodes data and promocodes
   	
   $joomla(document).on('click','.cupn-cde a',function(e){
   	    e.preventDefault();
   	    $joomla('.cupn-cde p').show();
   	   $joomla.ajax({
   	        url: "<?php echo JURI::base(); ?>index.php?option=com_userprofile&task=user.get_ajax_data&jpath=<?php echo urlencode(JPATH_SITE); ?>&pseudoParam="+new Date().getTime(),
   			data: { "getcouponcodeflag":1,"user":"<?php echo $user; ?>","amount":$joomla("input[name='amount']").val(),"volmetStr":$joomla("input[name='volmetStr']").val(),"volStr":$joomla("input[name='volStr']").val(),"qtyStr":$joomla("input[name='qtyStr']").val(),"wtStr":$joomla("input[name='weightStr']").val(),"shippingCost":$joomla("input[name='shipmentCost']").val()},
   			dataType:"html",
   			type: "get",
   			beforeSend: function() {
                       $joomla(".page_loader").show();
                   },    
   			success: function(data){
   			    $joomla(".page_loader").hide();
   			    $joomla(".cupn-cdes").show();
   			    $joomla(".cupn-cdes").html(data);
   			}
   		}); 
   		$joomla(this).hide();
   	});
   	
   	//get alert
  

 
 

//  $joomla("#j_table").on('click','.discardShip',function(){
//       $joomla(this).closest('td').remove();
//      });

//   $joomla('.updat_txt').click(function(){
//           $joomla('.invoice_pic_name').html('').remove();
//         //   $joomla('.invoice_pic_name').hide();
 
//   });

   	
   	
           // Initialize form validation on the registration form.
           // It has the name attribute "registration"
           // Initialize form validation on the registration form.
           // It has the name attribute "registration"
           var validseven=$joomla("form[name='userprofileFormSeven']").validate({
           
           // Specify validation rules
           rules: {
             // The key name on the left side is the name attribute
             // of an input field. Validation rules are defined
             // on the right side
             fnameTxt: {
               required: true,
               alphanumeric:true
             },
             lnameTxt: {
               required: true,
               alphanumeric:true
             },
             country2Txt: {
               selectBox: true
             },
             state2Txt: {
               required: true
             }/*,
             city2Txt: {
               selectBox: true
             },
             zipTxt: {
               required: true
             }*/,
             addressTxt: {
               required: true
             },
             idtypeTxt: {
               required: true
             },
             idvalueTxt: {
               required: true
             },
             emailTxt: {
               required: true,
               validateEmail:true
             }
       },
           // Specify validation error messages
           messages: {
             fnameTxt: {required:"<?php echo $assArr['first_name_error'];?>",alphanumeric:"<?php echo Jtext::_('COM_USERPROFILE_SHIP_POPUP_FIRSTNAME_ALPHABET_ERROR');?>"},
             lnameTxt: {required:"<?php echo $assArr['last_Name_error'];?>",alphanumeric:"<?php echo Jtext::_('COM_USERPROFILE_SHIP_POPUP_LASTNAME_ALPHABET_ERROR');?>"},
             country2Txt: {selectBox:"<?php echo $assArr['country_error'];?>"},
             state2Txt: {required:"<?php echo $assArr['state_error'];?>"},
             /*city2Txt: {selectBox:"Please select city"},
             zipTxt: "Please enter zip code",*/
             idtypeTxt: {required: "<?php echo $assArr['identification_type_error'];?>"},
             idvalueTxt: {required: "<?php echo $assArr['identification_value_error'];?>"},
             addressTxt: "<?php echo $assArr['additional_address_error'];?>",
             emailTxt: {required:"<?echo Jtext::_('COM_USERPROFILE_SHIP_POPUP_FIRSTNAME_ALPHABET_ERROR');?>",email:"<?php echo Jtext::_('COM_USERPROFILE_SHIP_POPUP_EMAIL_FORMAT_ERROR');?>"}
           },
           // Make sure the form is submitted to the destination defined
           // in the "action" attribute of the form when valid
           submitHandler: function(form) {
               $joomla(".page_loader").show();
          		$joomla("input[name=fnameTxt] #errorTxt-error").html('');
    		    $joomla.ajax({
   				url: "<?php echo JURI::base(); ?>index.php?option=com_userprofile&task=user.get_ajax_data&userid=<?php echo $user;?>&fnameid="+$joomla("#fnameTxt").val() +"&lnameid="+$joomla("#lnameTxt").val() +"&fnameflag=1&jpath=%2Fhome%2Fdemodelivery%2Fpublic_html&pseudoParam="+new Date().getTime(),
   				data: { "lnameid": $joomla("#lnameTxt").val(),"fnameid": $joomla("#fnameTxt").val() },
   				dataType:"html",
   				type: "get",
   				cache: false,             
                   processData: false,
                   beforeSend:function(){
                       $joomla(".page_loader").show();
                   },
   				success: function(data){
   				    $joomla(".page_loader").hide();
   				    if(data==1){
   				      if($joomla("#fnameTxt #errorTxt-error")){
   				       $joomla('#fnameTxt').after('<label id="errorTxt-error" class="error" for="errorTxt">Your Fullname already existed! </label>');    
   				      }    				      
   				    }else{
   				      if($joomla("#fnameTxt #errorTxt-error")){
   				       $joomla("#fnameTxt #errorTxt-error").html('');
   				      }
   				      form.submit(); 
   				    }
   				}
   			});
             
           }
      
           }); 
           
          
              //validation for email fields
	$joomla.validator.addMethod(
	"validateEmail", 
	function(value, element) {
	    const re = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
        return re.test(value);
		},
		"<?php echo JText::_('COM_USERPROFILE_PLEASE_ENTER_VALID_EMAIL_ADDRESS');?>"
	);
          
           
   
   
       $joomla("input[name='zipTxt']").live('keypress',function (e) {
         if(e.which == 46){
           if($joomla(this).val().indexOf('.') != -1) {
               return false;
           }
         }
         if (e.which != 8 && e.which != 0 && e.which != 46 && (e.which < 48 || e.which > 57)) {
           return false;
         }
       });
        $joomla('#exampleModal .btn-primary:first').live('click',function(){
            $joomla(".page_loader").show();
           if(validseven.form()==true){
               $joomla("input[name=fnameTxt] #errorTxt-error").html('');
    		    $joomla.ajax({
   				url: "<?php echo JURI::base(); ?>index.php?option=com_userprofile&task=user.get_ajax_data&userid=<?php echo $user;?>&fnameid="+$joomla("#fnameTxt").val() +"&lnameid="+$joomla("#lnameTxt").val() +"&fnameflag=1&jpath=%2Fhome%2Fdemodelivery%2Fpublic_html&pseudoParam="+new Date().getTime(),
   				data: { "lnameid": $joomla("#lnameTxt").val(),"fnameid": $joomla("#fnameTxt").val() },
   				dataType:"html",
   				type: "get",
   				cache: false,             
                   processData: false, 
   				success: function(data){
   				    console.log(data);
   				    if(data==1){
   				      //if($joomla("#fnameTxt #errorTxt-error")){
   				       alert('Your Fullname already existed');
   				       //$joomla('#fnameTxt').after('<label id="errorTxt-error" class="error" for="errorTxt"><?php echo Jtext::_('COM_USERPROFILE_EXIST_FULLNAME');?> </label>');    
   				       return false;
   				      //}    				      
   				    }else{
   				      var body=$joomla('#exampleModal .modal-body').html();
   				    //   var city2Txt=$joomla('input[name="city2Txtdiv"]').val();
   				      
   				    //   if(city2Txt == ''){
   				    //       city2Txt = $joomla('input[name="city2Txt"]').val();
   				    //   }
   				      var city2Txt = $joomla('select[name="city2Txt"]').val();
                         $joomla.ajax({
                   	        url: "<?php echo JURI::base(); ?>index.php?option=com_userprofile&task=user.get_ajax_data&getadduserpayflag=1&user=<?php echo $user;?>&typeuser="+$joomla("input[name='typeuserTxt']").val()+"&id="+$joomla("input[name='idTxt']").val()+"&fname="+$joomla("input[name='fnameTxt']").val()+"&lname="+$joomla("input[name='lnameTxt']").val()+"&country="+$joomla('#country2Txt').val()+"&state="+$joomla('#state2Txt').val()+"&city="+city2Txt+"&zip="+$joomla("input[name='zipTxt']").val()+"&address="+$joomla('#addressTxt').val()+"&address2="+$joomla('#address2Txt').val()+"&email="+$joomla("input[name='emailTxt']").val()+"&idtypetxt="+$joomla("#idtype2Txt").val()+"&identitytypetxt="+$joomla("input[name='identity2Txt']").val()+"&cpftxt="+$joomla("input[name='cpfTxt']").val()+"&cnpjtxt="+$joomla("input[name='cnpjTxt']").val()+"&idtypeTxt="+$joomla("select[name='idtypeTxt']").val()+"&idvalueTxt="+$joomla("input[name='idvalueTxt']").val()+"&jpath=<?php echo urlencode  (JPATH_SITE); ?>&pseudoParam="+new Date().getTime(),
                   			data: { "usertype":1},
                   			dataType:"html",
                   			type: "get",
                   			beforeSend: function() {
                   			   //$joomla('#exampleModal .modal-body').html('<div id="loading-image4" ><img src="<?php echo JURI::base(); ?>/components/com_userprofile/images/loader.gif"></div>');
                            	},    
                   			success: function(data){
                   			    //$joomla('#exampleModal .modal-body').html(body);
                   			    if(data=="Additional address successfully inserted"){
                   			        $joomla('#exampleModal').modal('toggle');
                   			        loadadditionalusersData();
                   			    }
                   			}
                   		});
   				    }
   				}
   			});
           }else{
               $joomla(".page_loader").hide();
           }	
        });    
       
       
        
       $joomla("input[name='cardnumberStr']").keyup(function(e){
           this.value = this.value.replace(/[^0-9]/g, '');
       });
   
       $joomla("input[name='txtccnumberStr']").keyup(function(e){
           this.value = this.value.replace(/[^0-9]/g, '');
      });
   
       $joomla(".ishipment").click(function(){
          $joomla(".page_loader").show();
          $joomla(".pageup").hide();
          $joomla("#dfg").remove();  
   
          $joomla('#step1').hide(); 
          $joomla('#step1 #j_table tbody').remove();    
          $joomla('#ord_ship #k_table tbody').remove(); 
          $joomla('#ord_ship #ki_table tbody').remove(); 
          $joomla('#specialinstructionStr').val('');
          $joomla('#fnameTxt').val('');
          $joomla('#lnameTxt').val('');
          $joomla('#zipTxt').val('');
          $joomla('#addressTxt').val('');
          $joomla('#emailTxt').val('');
          $joomla('[name="consignidStr"]').val('');
          $joomla(".txtId").prop( "checked", false );
          if($joomla('input[name="shipment"]:checked').val()=="1"){
              $joomla('#tabs2 #j_table tbody tr#dfg').remove();
          }else{
              $joomla('#tabs2 #u_table tbody tr#dfg').remove();
          }   
      
           $joomla.each($joomla("input[name='txtId']"), function(){ 
             $joomla(this).closest('tr').find('input[name="txtId"]').prop('checked',false);
           })
           $joomla("#ord_ship .ship").click(true); 
           $joomla.each($joomla(".wrchild"), function(){ 
               $joomla('.wrchild').val('+');
           })    
   
           
           if($joomla(this).val()==2){
               setTimeout(function(){
                  $joomla(".page_loader").hide();
                  $joomla(".pageup").show();
                   $joomla(".ishpments").show();
                   $joomla(".ishpments2").hide();
                   $joomla('.btn-danger').click();
               },3000);
           }else
           {
               setTimeout(function(){
                  $joomla(".page_loader").hide();
                  $joomla(".pageup").show();
                   $joomla(".ishpments2").show();
                   $joomla(".ishpments").hide();
               },3000);
           }
       })
   
      $joomla(".wrchild").click(function(){
           $joomla(this).closest('tr').find('td:eq(2)').text($joomla(this).closest('tr').find('input[name="txtQty"]').val());
           if($joomla('input[name="shipment"]:checked').val()=="1"){
               var rs=$joomla(this).closest('tr').find('td:eq(1)').text();
               var hj=$joomla(this).closest('tr').find('input[name="txtId"]').is(':checked');
               var htmse='';
               $joomla('#tabs2 #j_table tbody tr').each( function () {
                   if($joomla(this).attr('id')==rs){ 
                     htmse+='<tr id="dfg">'+$joomla(this).html()+'</tr>';
                     //$joomla('#dfg').find('input[name="txtId"]').prop('checked',true);
                     $joomla('#dfg').find('input[name="txtId"]').css('pointer-events','none');
                     $joomla('#dfg').find('input[name="txtId"]').prop('readonly',true);
                   }
               })
               if($joomla(this).val()=='-'){
                  var rmv=1;
                  $joomla.each($joomla('#tabs2 #j_table tbody tr#dfg'), function(){
                     if($joomla(this).closest('tr').find('td:eq(1)').text()==rs){ 
                         console.log(hj);
                         if(hj){ 
                             rmv=0;
                         }else{
                             $joomla(this).closest('tr').remove();
                         }
                       }
                   })
                   if(rmv==1){
                       $joomla(this).val('+');
                   }
                   $joomla(this).closest('tr').find('td:eq(2)').text($joomla(this).closest('tr').find('input[name="txtItemQty"]').val());
               }else{
                    //$joomla(this).closest('tr').after(htmse); 
                   $joomla(this).val('-');
               }     
           }else{
   
               var rs=$joomla(this).closest('tr').find('td:eq(1)').text();
               var hj=$joomla(this).closest('tr').find('input[name="txtId"]').is(':checked');
              var htmse='';
               $joomla('#tabs2 #u_table tbody tr').each( function () {
                   if($joomla(this).attr('id')==rs){ 
                     htmse+='<tr id="dfg">'+$joomla(this).html()+'</tr>';
                     $joomla('#dfg').find('input[name="txtId"]').prop('checked',true);
                     $joomla('#dfg').find('input[name="txtId"]').css('pointer-events','none');
                     $joomla('#dfg').find('input[name="txtId"]').prop('readonly',true);
                   }
               })
               if($joomla(this).val()=='-'){
                  var rmv=1;
                  $joomla.each($joomla('#tabs2 #u_table tbody tr#dfg'), function(){
                     if($joomla(this).closest('tr').find('td:eq(1)').text()==rs){ 
                         console.log(hj);
                         if(hj){ 
                             rmv=0;
                         }else{
                             $joomla(this).closest('tr').remove();
                         }
                       }
                   })
                   if(rmv==1){
                       $joomla(this).val('+');
                   }
                   $joomla(this).closest('tr').find('td:eq(2)').text($joomla(this).closest('tr').find('input[name="txtItemQty"]').val());
               }else{
                    //$joomla(this).closest('tr').after(htmse); 
                   $joomla(this).val('-');
               }     
   
           }    
       });
     
       var r=0;
       $joomla('#tabs2 #j_table .wrchild').each( function () {
               var rs=$joomla(this).closest('tr').find('td:eq(1)').text();
               $joomla('#tabs2 #j_table tbody tr').each( function () {
                   if($joomla(this).attr('id')==rs){ 
                       r+=parseInt($joomla(this).closest('tr').find('input[name="txtQty"]').val());
                   }
               })
               r=parseInt($joomla(this).closest('tr').find('input[name="txtQty"]').val())+r;
           console.log(r);
           $joomla(this).closest('tr').find('td:eq(2)').text(r);
           $joomla(this).closest('tr').find('input[name="txtItemQty"]').val(r);
           r=0;
           //r++;
       });
       var rr=0;
       $joomla('#tabs2 #u_table .wrchild').each( function () {
             var rs=$joomla(this).closest('tr').find('td:eq(1)').text();
             $joomla('#tabs2 #u_table tbody tr').each( function () {
                   if($joomla(this).attr('id')==rs){ 
                       rr+=parseInt($joomla(this).closest('tr').find('input[name="txtQty"]').val());
                   }
               })
               rr=parseInt($joomla(this).closest('tr').find('input[name="txtQty"]').val())+rr;
           console.log(rr);
           $joomla(this).closest('tr').find('td:eq(2)').text(rr);
           $joomla(this).closest('tr').find('input[name="txtItemQty"]').val(rr);
           rr=0;
       });
   
   
       $joomla(function() {
    
           
           // Initialize form validation on the registration form.
           // It has the name attribute "registration"
           $joomla("form[name='userprofileFormSix']").validate({
           rules: {
             // The key name on the left side is the name attribute
             // of an input field. Validation rules are defined
             // on the right side
             "article2Str[]": {
               required: true/*,
               alphanumeric:true*/
             },
             "price2Str[]": {
               required: true
             }
           },
           // Specify validation error messages
           messages: {
             "article2Str[]": {required:"Please enter item description"/*,alphanumeric:"The item description must contain alphabet characters only."*/},
             "price2Str[]": "Please enter Declared Value"
           },
           
           // Specify validation rules
           // Make sure the form is submitted to the destination defined
           // in the "action" attribute of the form when valid
           submitHandler: function(form) {
   
               var sdf=0;
               $joomla("input[name='price2Str[]']").each( function () {
                   if($joomla(this).val()=="0.00"){             
                       alert("Please enter Declared Price Value($)");
                       sdf=1;
                       return false;
                   }    
               });    
               // $joomla("input[name='inv2File[]']").each( function () {
               //   if( $joomla(this).closest("tr").find(".sfile").length=="0"){
               //         if($joomla(this).val()==""){             
               //             alert("please upload Invoice");
               //             sdf=1;
               //             return false;
               //         }    
               //   }
               // })
               if(sdf==0){
                   var articlestrs=[];
                   $joomla.each($joomla("input[name='article2Str[]']"), function(){
                       articlestrs.push($joomla(this).val());
                   });    
                   var pricestrs=[];
                   $joomla.each($joomla("input[name='price2Str[]']"), function(){
                       pricestrs.push($joomla(this).val());
                   });    
                   
                   var user="<?php echo $user;?>";
                   $joomla('#loading-image5').show();
       	        $joomla('#loading-image5').focus();
                   $joomla('#ord_ship .modal-body').hide();
                   $joomla('#ord_ship .modal-body2').html('<img src="/components/com_userprofile/images/loader.gif" width="400" height="400">');
   
       	        form.submit();
               }else{
                   return false;
               }
           }
           
           });    
       });
       
// expand all

$joomla(document).ready(function(){	
 $joomla(".child_row").hide();

$joomla('#j_table')
.on( 'draw.dt',function () {
    $joomla(".child_row").hide();
      if($joomla('#expandAll').prop("checked") == true){
          $joomla(".expand_all_btn:visible").addClass("expand_icon");
          $joomla(".expand_all_btn").attr("title","Expand All");
          $joomla(".expand_all_btn:visible").removeClass("collapse_icon");
      }
      $joomla(".exp_item").each(function(){
         var content = $joomla(this).html();
         if(content == "-"){
         $joomla(this).trigger("click");
         }
      });
      $joomla("#colspantd1,#colspantd2,#colspantd3").addClass("colspan").attr("colspan",$joomla('.mainheader1 th').length);
})
.dataTable();
     $joomla('#j_table')
     .on( 'length.dt',function () {
         $joomla(".exp_item").each(function(){
         var content = $joomla(this).html();
         if(content == "-"){
         $joomla(this).trigger("click");
         }
     });
     
     if($joomla('#expandAll').prop("checked") == true){
         $joomla('#expandAll').prop("checked",false);
     }

     $joomla("#colspantd1,#colspantd2,#colspantd3").addClass("colspan").attr("colspan",$joomla('.mainheader1 th').length);
       
     })
     .dataTable();
 $joomla('#j_table')
 .on( 'search.dt', function () {
    $joomla("#colspantd1,#colspantd2,#colspantd3").addClass("colspan").attr("colspan",$joomla('.mainheader1 th').length);
 })
 .dataTable();


});	

 $joomla(document).on('click','.exp_item',function(e){
// $joomla(".exp_item").on('click',function(){	
var btnCount=$joomla(".exp_item:visible").length;	
if($joomla(this).html() == "-"){	
      $joomla("#expandAll:visible").prop("checked",false);	
      $joomla(".expand_all_btn:visible").addClass("expand_icon");
      $joomla(".expand_all_btn").attr("title","Expand All");
      $joomla(".expand_all_btn:visible").removeClass("collapse_icon");
      
      }	
 $joomla(this).parent().parent().next().toggle();	
  wrhsno = $joomla(this).attr("data-id");	
  repackno = $joomla(this).attr("data-repack");	
  $joomla('.txtId:visible').each(function(){	
                    var valueStr = $joomla(this).val();	
                    var valueArr = valueStr.split(":");	
                    if(wrhsno == valueArr[1] && (repackno =="" || repackno == undefined )){	
                        $joomla(this).closest(".exp_item:visible").toggle();	
                    }	
                    	
                    if(repackno == valueArr[31] && repackno !=""){	
                        $joomla(this).closest(".exp_item:visible").toggle();	
                    }	
         });	
if($joomla(this).html() == '+'){	
             $joomla(this).html('-');	
             $joomla(this).parent().parent().next().show();	
         }else{	
             $joomla(this).html('+');	
             $joomla(this).parent().parent().next().hide();	
         }	
        	
          	
      var count=0;	
      $joomla(".exp_item:visible").each(function(){	
         if($joomla(this).html() == "-"){	
         count++;	
         }	
      });	
     	
         if(count == btnCount){	
            $joomla('.expand_all_btn:visible').trigger("click");
           
         }	
     	
});



    
    // pending records  

 $joomla(document).on('click','.expand_all_btn',function(e){
      var rowCount = $joomla("table tbody .exp_item:visible ").length;
      if(rowCount < 1){
            alert("no records to found expand");
            e.preventDefault();
            return false;
             }
    // $joomla('.expand_all_btn').on('click',function(e){
        $joomla('#expandAll').trigger("click");
        if($joomla('#expandAll').prop("checked") == true){
            $joomla(this).addClass("collapse_icon");
            $joomla(this).attr("title","Collapse All");
            $joomla(this).removeClass("expand_icon");	
        }else{
            $joomla(this).addClass("expand_icon");
            $joomla(this).attr("title","Expand All");
            $joomla(this).removeClass("collapse_icon");	
        }
    });
    
   
 $joomla(document).on('click','#expandAll',function(e){
    // $joomla('#expandAll').on('click',function(e){
       
        if($joomla(this).prop("checked") == true){
            $joomla(".exp_item:visible").each(function(){
                var expCont = $joomla(this).html();
                if(expCont == "+"){
                    $joomla(this).trigger("click");
                }
            });
        }else{
            $joomla(".exp_item:visible").each(function(){
                var expCont = $joomla(this).html();
                if(expCont == "-"){
                $joomla(this).trigger("click");

                }
            });
        } 
    });
       
       
      
   });
   
   function loadadditionalusersData(){
       $joomla(document).ready(function() {    
    	    $joomla.ajax({
   	        url: "<?php echo JURI::base(); ?>index.php?option=com_userprofile&task=user.get_ajax_data&user=<?php echo $user;?>&getadduserdataflag=1&jpath=<?php echo urlencode  (JPATH_SITE); ?>&pseudoParam="+new Date().getTime(),
   			data: { "useridtypes":1},
   			dataType:"html",
   			type: "get",
   			beforeSend: function() {
                   //$joomla(".pagshipup").show();
                   $joomla(".page_loader").show();
                   $joomla('.pagshipdown').hide();
               },    
               success: function(data){
                   //$joomla(".pagshipup").hide();
                   $joomla(".page_loader").hide();
                   $joomla('.pagshipdown').show();
   			  $joomla('#additionalusersData').html('<select class="form-control" name="adduserlistStr">'+data+'</select>');
   			  
   			}
   		}); 
       });
     
   }
   //onKeyPress="return isNumber(event)" 
   function isNumber(evt) {
       evt = (evt) ? evt : window.event;
       var charCode = (evt.which) ? evt.which : evt.keyCode;
       if (charCode > 31 && (charCode < 48 || charCode > 57)) {
           return false;
       }
       return true;
   }
   
   
</script>


<div class="container pagein" style="display:none">
   <div class="main_panel persnl_panel">
      <div class="panel-body">
         <div class="row">
            <div class="col-sm-12">
               <img src='/components/com_userprofile/images/loader.gif' height="400" width="400">
            </div>
         </div>
      </div>
   </div>
</div>
<div class="container pageup">
   <div class="main_panel persnl_panel">
      <div class="main_heading"><?php echo $assArr['items_in_Stock'];?></div>
      <div class="panel-body">

          
         <div class="row">
            <div class="col-sm-12 tab_view">
               <ul class="nav nav-tabs">
                  <?php 
                  
                  foreach($clients as $client){ 
                        if(strtolower($client['Domain']) == strtolower($domainName) ){   
                            $prealert_text=$client['Myprealerts_text_dashboard'];
                            // $return_enable=$client['Return_enable'];
                            // $hold_enable=$client['Hold_enable'];
                            $change_address_enable=$client['change_address_enable'];
                            // $prepaid_text=$client['Prepaid_text'];
                            // $Payment_gateway_dynamic_enable=$client['Payment_gateway_dynamic_enable']; 
                            //$Gross_weight_display=$client['Gross_weight_display'];
                            // $promocodes = $client['Promocodes'];
                            $OrderId = $client['OrderId'];
                            $RmaVal = $client['RmaVal'];
                        }
                    }
                  
                  if(!isset($maccarr['FulFillment'])){
                      $maccarr['FulFillment'] = "False";
                  }
                  
                    
                  //if(($menuCustType == "CUST" && $dynpage["PreAlerts"][1]=="ACT") || ($menuCustType == "COMP" && $maccarr['FulFillment'] == "False" && $dynpage["PreAlerts"][1]=="ACT") ){  ?>
                  <!--<li> <a class="" href="index.php?option=com_userprofile&view=user&layout=orderprocessalerts"><?#php echo $assArr['my_Pre_Alerts'];?></a> </li>-->
                  <?#php }else if($menuCustType == "COMP" && $maccarr['FulFillment'] == "True"){  ?>
                  <!--<li> <a class="" href="index.php?option=com_userprofile&view=user&layout=inventoryalerts"><?#php echo $assArr['inventory_Pre-Alerts'];?></a> </li>-->
                  <?#php } if($dynpage["PendingShipments"][1]=="ACT"){ ?>
                  <!--<li> <a class="active" href="index.php?option=com_userprofile&view=user&layout=orderprocess"><?#php echo $assArr['ready_to_ship'];?></a> </li>-->
                   <?#php } if($dynpage["COD"][1]=="ACT"){ ?>
                  <!--<li> <a class=""  href="index.php?option=com_userprofile&view=user&layout=cod"> <?#php echo $assArr['cOD'];?> </a> </li>-->
                  <?#php } if($dynpage["ShipmentHistory"][1]=="ACT"){ ?>
                  <!--<li> <a class="" href="index.php?option=com_userprofile&view=user&layout=cod">COD</a> </li>-->
                  <!--<li> <a class="" href="index.php?option=com_userprofile&view=user&layout=shiphistory"><?#php echo $assArr['shipment_History'];;?></a> </li>-->
                  <?#php } ?>
               </ul>
            </div>
         </div>
         <div id="tabs2">
            <div class="alertSection" style="display:none;">          
                <div class="alert alert-success" id="success-alert">
                    <strong></strong>
                </div>
            </div>
            <div class="alertfailSection" style="display:none;">          
                <div class="alert alert-danger" id="success-danger">
                    <strong></strong>
                </div>
            </div>
            <div class="clearfix"></div>
            
            <div class="row ishpments2">

<div class="">

   <div class="col-sm-12 inventry-item">

     <div class="col-sm-3">

            <h3 class=""><strong><?php echo Jtext::_('COM_USERPROFILE_SHIP_SUB_TITLE');?></strong></h3>

     </div>

        <div class="col-sm-9 form-group text-right inventry-item-right">
             <div class="action-icons pull-right btn-action-groupnew">
 
                <a target="_blank" href="<?php echo $backend_url; ?>/ASPX/Tx_inventoryReceipt.aspx?bid=<?php echo $user; ?>&companyid=<?php echo $CompanyId; ?>"  title="Inventory Report"  class="btnallaction csvDownload inventory_icon"></a>
                
                <input type="checkbox" name="expandAll" id="expandAll"><button class="btnallaction expand_all_btn expand_icon" data-toggle="tooltip" title="Expand All"></button>

            </div>
        </div>

    </div>
</div>
                
            <div class="col-md-12">
               <div class="table-responsive">
                     <table class="table table-bordered theme_table" id="j_table" data-page-length='10'>
                        <thead>
                           <tr class="mainheader1"> 
                           <th class="action_btns text-center"><input type="checkbox"  style="display:none" name="selectAll" id="selectAll"> <?php echo $assArr['select_All'];?></th>
                             <th><?php echo $assArr['warehouse_Receipt'];?>#</th>
                             <th><?php echo $assArr['item_Description'];?></th>
                             <th><?php echo $assArr['tracking_ID'];?></th>
                             <th><?php echo $assArr['merchants_Name'];?></th>
                             <th><?php echo $assArr['type_of_shipment'];?></th>
                             <th><?php echo $assArr['source_Hub'];?></th>
                             <th><?php echo $assArr['destination'];?></th>
                             <th><?php echo $assArr['destination_Hub'];?></th>
                             <th><?php echo $assArr['wt_Units'];?></th>
                             <th><?php echo $assArr['measurement_Units'];?></th>
                             <th><?php echo $assArr['Business_Type']; ?></th>
                             <?php if($Gross_weight_display){ ?>
                             <th><?php echo $assArr['gROSS_WT'];?></th>
                               <?php } ?>
                           </tr>
                           
                        </thead>
                        <tbody>
                        <?php
                            $ordersPendingView= UserprofileHelpersUserprofile::getOrdersPendingList($user);
                            foreach($ordersPendingView as $repack){
                              if($repack->InhouseRepackLbl == ""){
                                   
                                   $itemsListtArr = array();
                                   $k=0;
                                   foreach($repack->WarehouseDetails as $res){
                                       
                                        $itemDesStrLoop = "";
                                        $grossWtTot=0.00;
                                        foreach($res->ItemDetails as $rg){
                                            $itemDesStrLoop .= str_replace(",","-",$rg->ItemName).",";
                                            $grossWtTot += (float)($rg->GrossWeight);
                                        }
                                        $itemDesStrLoop = rtrim($itemDesStrLoop,",");
                                        $itemsListtArr[$k] = $itemDesStrLoop;
                                        $grossWtArray[$k]=$grossWtTot;
                                        $k++;
                                   }
                                  $i=0;
                                  foreach($repack->WarehouseDetails as $res){
                                      
                                              if(strlen($itemsListtArr[$i]) > 20){
                                             $itemsListtArr[$i] = '<span class="wrhsItemDetails completeDetails" data-val="'.$itemsListtArr[$i].'">'.substr($itemsListtArr[$i],0,20)."...</span>";
                                              }
                               echo '';  
                               $addHidden=UserprofileHelpersUserprofile::getBindShipingAddress($user,$res->BillFormNo);
                            //   var_dump($addHidden);
                            //   exit;
                               echo'<tr> 
                              <td ><button class="exp_item btn btn-success" data-sno="item_wr_'.$i.'"  data-id="'.$res->BillFormNo.'">+</button><input type="checkbox" style="display:none" class="selinpt-chk check_all_items" data-id="'.$res->BillFormNo.'"></td>
                              <td id="txtbiladdress" data-address="'.$addHidden.'"><span  class="whrse-link label-success">'.$res->BillFormNo.'</span></td>
                              <td>'.$itemsListtArr[$i].'</td>
                              <td>'.$res->TrackingId.'</td>
                              <td>'.$res->MerchantName.'</td>
                              <td>'.$res->ShipmentType.'</td>
                              <td>'.$res->SourceHub.'</td>
                              <td>'.$res->DestinationCountryName.'</td>
                              <td>'.$res->DestinationHubName.'</td>
                              <td>'.$res->WeightUnit.'</td>
                              <td>'.$res->DimUnits.'</td>
                              <td>'.$res->BusinessType.'</td>';
                              if($Gross_weight_display){
                               echo '<td>'.number_format((float)$grossWtArray[$i], 2, '.', '').'</td>';
                              }
                              echo'</tr>';

                              $OrderIdHead = Null;
                              if($OrderId){
                                 $OrderIdHead = '<th>'.$assArr["order_ID"].'</th>';
                              }
                              $RmaValHead = Null;
                              if($RmaVal){
                                 $RmaValHead = '<th>'.$assArr["rMA_Value"].'</th>';
                              }
                             
                              if($Gross_weight_display){
                                 $GrossWeightHead = '<th>'.$assArr["gROSS_WT"].'</th>';
                              }
                              $DeclaredValHead = Null;
                              if($DeclaredValDisplay){
                                 $DeclaredValHead = '<th>'.$assArr["Declared Value (USD)"].'</th>';
                              }

                             echo'<tr class="child_row "><td id="colspantd1" >
                             <table class="table table-bordered"> 
                        	  <tr class="wrhuse-grid">
                             <th colspan="2">'.$assArr['action'].'</th>
                              <th>'.$assArr['item_Description'].'</th>
                              <th>'.$assArr['quantity'].'</th>
                              <th>'.$assArr['ship_quantity'].'</th>
                              <th>'.$assArr['tracking_ID'].'</th>
                              <th>'.$assArr['merchants_Name'].'</th>'.$OrderIdHead.$RmaValHead;
                              echo '<th>View / Update Invoice</th>'.$GrossWeightHead.$DeclaredValHead;
                              echo '<th></th><th></th></tr>';

                               $idf=1;

                             foreach($res->ItemDetails as $rg){
                                  
                              //   var_dump($rg->ItemImage1);
                              //   exit;
                             
                              if(1){ // $rg->ItemQuantity>0
                                  $volres=$rg->Height*$rg->Width*$rg->Length*UserprofileHelpersUserprofile::getShippmentDetailsValues($repack->MeasureUnits,$repack->shipment_type,$repack->ServiceType,$repack->Source,$repack->Dest_Cntry);
                                  if($repack->shipment_type=="AIR"){
                                    $volres=$rg->ItemQuantity*$volres;
                                  }
                                  if($rg->ItemImage1==""){
                                      $sim=1; 
                                      $mgtd='<td>No Image</td>';
                                  }else{
                                          $sim=str_replace(":","#",$rg->ItemImage1);
                                          $sim1=str_replace(":","#",$rg->ItemImage2);
                                          $sim2=str_replace(":","#",$rg->ItemImage3);
                                          $sim3=str_replace(":","#",$rg->ItemImage4);
                                          $mgtd='<td class="action_btns"><a class="ship_img" data-idk="'.$rg->ItemIdk.'" data-toggle="modal" data-backdrop="static" data-keyboard="false" href="#" data-id="'.$rg->ItemImage1.'##'.$rg->ItemImage2.'##'.$rg->ItemImage3.'##'.$rg->ItemImage4.'" data-target="#view_image" ><i class="fa fa-eye"></i></a></td>';
                                    }
                                  // gross wt display
                                  $grosswtTd = NULL;
                                 if($Gross_weight_display){
                                    $grosswtTd = "<td>".$rg->GrossWeight."</td>";
                                 }  

                                 //  declared val display
                                 $declaredVal = NULL;
                                 if($DeclaredValDisplay){
                                    $declaredVal = "<td>".$rg->TotalPrice."</td>";
                                 }  

                                 //  order id display
                                 $orderIdDis = NULL;
                                 if($OrderId){
                                    $orderIdDis = "<td width='100px'>".$rg->OrderID."</td>";
                                 }  

                                 //  rma value display
                                 $rmaValDis = NULL;
                                 if($RmaVal){
                                    $rmaValDis = "<td width='100px'>".$rg->RmaValue."</td>";
                                 }  

                               $readonltTxt = "";
                               if($rg->InhouseRepacklbl !=''){
                                  $readonltTxt = "readonly";
                               }

                           echo'<tr>
                              
                              <td colspan="2" class="action_btns">
                              <input type="checkbox" style="display:none" name="txtId" class="txtId selinpt-chksub" data-sno="item_wr_'.$idf.'"  value="'.$rg->ItemName.':'.$res->BillFormNo.':'.$rg->ItemQuantity.':'.$res->TrackingId.':'.$rg->ItemIdk.':'.$rg->cost.':'.$rg->cost.':item_wr_'.$idf.':'.$volres.':'.$repack->ServiceType.':'.$repack->Source.':'.$repack->Dest_Cntry.':'.$repack->MeasureUnits.':'.$rg->Length.':'.$rg->Width.':'.$rg->Height.':'.$rg->GrossWeight.':'.$repack->WeightUnit.':'.$sim.':'.$repack->ShipmentType.':'.$repack->SourceHub.':'.$repack->DestinationCountryName.':'.$repack->DestinationHubName.':'.$rg->Insurance.':'.$rg->ItemPrice.':'.$sim1.':'.$sim2.':'.$sim3.':'.$repack->BusinessType.':'.$rg->Volume.':'.$rg->VolumetricWeight.':'.$rg->InhouseRepacklbl.':'.$repack->ServiceId.'">
                               <input type="button" style="display:none" name="ship" class="ship" data-sno="item_wr_'.$idf.'" data-id="'.$rg->ItemName.':'.$res->BillFormNo.':'.$rg->ItemQuantity.':'.$res->TrackingId.':'.$rg->ItemIdk.':'.$rg->cost.':'.$rg->cost.':item_wr_'.$idf.':'.$volres.':'.$repack->ServiceType.':'.$repack->Source.':'.$repack->Dest_Cntry.':'.$repack->MeasureUnits.':'.$rg->Length.':'.$rg->Width.':'.$rg->Height.':'.$rg->GrossWeight.':'.$repack->WeightUnit.':'.$sim.':'.$repack->ShipmentType.':'.$repack->SourceHub.':'.$repack->DestinationCountryName.':'.$repack->DestinationHubName.':'.$rg->Insurance.':'.$rg->ItemPrice.':'.$sim1.':'.$sim2.':'.$sim3.':'.$repack->BusinessType.':'.$rg->Volume.':'.$rg->VolumetricWeight.':'.$rg->InhouseRepacklbl.':'.$repack->ServiceId.'" data-target="#ord_ship" title="'.Jtext::_('COM_USERPROFILE_SHIP_HISTORY_STATUS_SHIP').'">';
                                if($elem['Hold'][1] == "ACT")
                                  echo '<input type="button" name="Return" class="return" data-toggle="modal" data-backdrop="static" data-keyboard="false" data-id="'.$res->BillFormNo.':'.$rg->ItemIdk.':'.$rg->ItemQuantity.'" data-target="#ord_return"" title="'.Jtext::_('COM_USERPROFILE_SHIP_HISTORY_STATUS_RETURN').'">';
                                if($elem['Return'][1] == "ACT")
                                  echo '<input type="button" name="Keep" class="keep" data-toggle="modal" data-backdrop="static" data-keyboard="false" data-id="'.$res->BillFormNo.':'.$rg->ItemIdk.':'.$rg->ItemQuantity.'" data-target="#ord_keep" " title="'.Jtext::_('COM_USERPROFILE_SHIP_HISTORY_STATUS_HOLD').'">';
                                  echo '</td>
                              <td width="100px">'.$rg->ItemName.'</td>
                              <td width="100px">'.$rg->ItemQuantity.'</td>
                              <td width="100px"><input type="hidden" name="ItemIdkTxt" value="'.$rg->ItemIdk.'"><input type="hidden" name="ItemQtyTxt" value="'.$rg->ItemQuantity.'"><input type="hidden" name="ItemQtyEdit" value="'.$rg->ItemQuantity.'"><input type="text" class="form-control" name="txtQty" value="'.$rg->ItemQuantity.'" ></td>
                              <td width="100px">'.$res->TrackingId.'</td>
                              <td width="100px">'.$res->MerchantName.'</td>'.$orderIdDis.$rmaValDis;
                              echo''.$mgtd.$grosswtTd.$declaredVal.'
                              <td width="100px"></td>
                              <td width="100px"></td>';
                              echo '</tr>';

                            $p=$res->BillFormNo;
                           }
                           $idf++;
                           
                     }
                     echo' </table></td><td style="display:none"></td><td style="display:none"></td>
                     <td style="display:none"></td>
                     <td style="display:none"></td>
                     <td style="display:none"></td>
                     <td style="display:none"></td>
                     <td style="display:none"></td>
                     <td style="display:none"></td>
                     <td style="display:none"></td>
                     <td style="display:none"></td>
                     <td style="display:none"></td>';
                      if($Gross_weight_display){
                               echo "<td style='display:none'></td>";
                            }
                            
                     echo '</tr>';
                              $i++;
                         }
                         }else{

                           // repack list 
                                    
                           $wrhsStrLoop = "";
                           $trackStrLoop = "";
                           $merchantStrLoop = "";
                           $itemDesStrLoop = "";
                           foreach($repack->WarehouseDetails as $res){
                               
                               $wrhsStrLoop .= $res->BillFormNo.",";
                               $trackStrLoop .= $res->TrackingId.",";
                               $merchantStrLoop .= $res->MerchantName.",";
                               
                               foreach($res->ItemDetails as $rg){
                                   $itemDesStrLoop .= str_replace(",","-",$rg->ItemName).",";
                               }
                               
                           }
                                    
                                    $wrhsStrLoop = rtrim($wrhsStrLoop,",");
                                    $trackStrLoop = rtrim($trackStrLoop,",");
                                    $merchantStrLoop = rtrim($merchantStrLoop,",");
                                    $itemDesStrLoop = rtrim($itemDesStrLoop,",");
                                    
                                                if(strlen($itemDesStrLoop) > 20){
                                                    $itemDesStrLoop = '<span class="wrhsItemDetails completeDetails" data-val="'.$itemDesStrLoop.'">'.substr($itemDesStrLoop,0,20)."...</span>";
                                                }
                                                if(strlen($trackStrLoop) > 20){
                                                    $trackStrLoop = '<span class="trackIdDetails completeDetails" data-val="'.$trackStrLoop.'">'.substr($trackStrLoop,0,20)."...</span>";
                                                }
                                                if(strlen($merchantStrLoop) > 20){
                                                    $merchantStrLoop = '<span class="merchNameDetails completeDetails" data-val="'.$merchantStrLoop.'">'.substr($merchantStrLoop,0,20)."...</span>";
                                                }
                                    
                                                $wrhsArr = explode(",",$wrhsStrLoop);
                                                
                                            	  if($repack->Status_Request == "Repack"){
                                            	    $backgroundColor = "background:#fff6c8";
                                              	}else{
                                            	    $backgroundColor = "background:#e2eed8";
                                              	}
                                              	
                                              	$addHidden=UserprofileHelpersUserprofile::getBindShipingAddress($user,$wrhsArr[0]);
                             
                     echo'<tr style="'.$backgroundColor.'"> 
                     <td><button  class="exp_item btn btn-success" data-sno="item_wr_'.$i.'"  data-id="'.$res->BillFormNo.'" data-repack="'.$repack->InhouseRepackLbl.'" >+</button><input type="checkbox" style="display:none"; class="selinpt-chk check_all_items_repack" data-id="'.$res->BillFormNo.'" data-repack="'.$repack->InhouseRepackLbl.'"></td>
                     <td id="txtbiladdress" data-address="'.$addHidden.'"><span class="repack-link label-success" data-val="'.$wrhsStrLoop.'">'.$repack->InhouseRepackLbl.'</span>';
                     if($repack->Status_Request == "Repack"){
                     echo '<input type="button" style="display:none" name="unpack" class="itemunpack" data-val="'.$wrhsStrLoop.'" title="Unpack" data-id="'.$repack->InhouseRepackLbl.'"  class="btn btn-warning"></td>';
                  }else{
                     echo '<input type="button" name="deconsolidation" class="itemunpack" data-val="'.$wrhsStrLoop.'" title="Deconsolidation" data-id="'.$repack->InhouseRepackLbl.'"  class="btn btn-warning"></td>';    
                      } 
                      echo '<td>'.$itemDesStrLoop.'</td>
                              <td>'.$trackStrLoop.'</td>
                              <td>'.$merchantStrLoop.'</td>
                              <td>'.$repack->ShipmentType.'</td>
                              <td>'.$repack->SourceHub.'</td>
                              <td>'.$repack->DestinationCountryName.'</td>
                              <td>'.$repack->DestinationHubName.'</td>
                              <td>'.$repack->WeightUnit.'</td>
                              <td>'.$repack->MeasureUnits.'</td>
                              <td>'.$repack->BusinessType.'</td>';
                              if($Gross_weight_display){ 
                              echo '<td>'.$repack->GrossWeight.'</td>';
                                  }
                             echo'</tr>';

                             $OrderIdHead = Null;
                              if($OrderId){
                                 $OrderIdHead = '<th>'.$assArr["order_ID"].'</th>';
                              }
                              $RmaValHead = Null;
                              if($RmaVal){
                                 $RmaValHead = '<th>'.$assArr["rMA_Value"].'</th>';
                              }
                             
                              if($Gross_weight_display){
                                 $GrossWeightHead = '<th>'.$assArr["gROSS_WT"].'</th>';
                              }
                              $DeclaredValHead = Null;
                              if($DeclaredValDisplay){
                                 $DeclaredValHead = '<th>'.$assArr["Declared Value (USD)"].'</th>';
                              }

                             echo'<tr class="child_row"><td id="colspantd2">
                             <table class="table table-bordered"> 
                        	  <tr class="wrhuse-grid">
                             <th colspan="2">'.$assArr['action'].'</th>
                              <th>'.$assArr['item_Description'].'</th>
                              <th>'.$assArr['quantity'].'</th>
                              <th>'.$assArr['ship_quantity'].'</th>
                              <th>'.$assArr['tracking_ID'].'</th>
                              <th>'.$assArr['merchants_Name'].'</th>'.$OrderIdHead.$RmaValHead;
                              echo '<th>View / Update Invoice</th>'.$GrossWeightHead.$DeclaredValHead;
                              echo '<th></th><th></th></tr>';

                              $idf=1;
                              
                             
                              foreach($repack->WarehouseDetails as $res){
                             
                                foreach($res->ItemDetails as $rg){
                                    
                                  //   var_dump($rg->ItemImage1);
                                  //   exit;
                                 
                                  if(1){ // $rg->ItemQuantity>0
                                      $volres=$repack->Height*$repack->Width*$repack->Length*UserprofileHelpersUserprofile::getShippmentDetailsValues($repack->MeasureUnits,$repack->shipment_type,$repack->ServiceType,$repack->Source,$repack->Dest_Cntry);
                                      if($repack->shipment_type=="AIR"){
                                        $volres=$rg->ItemQuantity*$volres;
                                      }
                                      if($rg->ItemImage1==""){
                                          $sim=1; 
                                          $mgtd='<td>No Image</td>';
                                      }else{
                                              $sim=str_replace(":","#",$rg->ItemImage1);
                                              $sim1=str_replace(":","#",$rg->ItemImage2);
                                              $sim2=str_replace(":","#",$rg->ItemImage3);
                                              $sim3=str_replace(":","#",$rg->ItemImage4);
                                              $mgtd='<td class="action_btns"><a class="ship_img" data-toggle="modal" data-backdrop="static" data-keyboard="false" href="#" data-id="'.$rg->ItemImage1.'##'.$rg->ItemImage2.'##'.$rg->ItemImage3.'##'.$rg->ItemImage4.'" data-target="#view_image" ><i class="fa fa-eye"></i></a></td>';
                                        }

                                      // gross wt display
                                  $grosswtTd = NULL;
                                 if($Gross_weight_display){
                                    $grosswtTd = "<td>".$rg->GrossWeight."</td>";
                                 }  

                                 //  declared val display
                                 $declaredVal = NULL;
                                 if($DeclaredValDisplay){
                                    $declaredVal = "<td>".$rg->TotalPrice."</td>";
                                 }  

                                 //  order id display
                                 $orderIdDis = NULL;
                                 if($OrderId){
                                    $orderIdDis = "<td width='100px'>".$rg->OrderID."</td>";
                                 }  

                                 //  rma value display
                                 $rmaValDis = NULL;
                                 if($RmaVal){
                                    $rmaValDis = "<td width='100px'>".$rg->RmaValue."</td>";
                                 }  
                              
                              
                               echo'<tr>
                              
                              <td colspan="2" class="action_btns">
                              <input type="checkbox" style="display:none"  name="txtId" class="txtId selinpt-chksub" data-sno="item_wr_'.$idf.'"  value="'.$rg->ItemName.':'.$res->BillFormNo.':'.$rg->ItemQuantity.':'.$res->TrackingId.':'.$rg->ItemIdk.':'.$rg->cost.':'.$rg->cost.':item_wr_'.$idf.':'.$volres.':'.$repack->ServiceType.':'.$repack->Source.':'.$repack->Dest_Cntry.':'.$repack->MeasureUnits.':'.$repack->Length.':'.$repack->Width.':'.$repack->Height.':'.$repack->Weight.':'.$repack->WeightUnit.':'.$sim.':'.$repack->ShipmentType.':'.$repack->SourceHub.':'.$repack->DestinationCountryName.':'.$repack->DestinationHubName.':'.$rg->Insurance.':'.$rg->ItemPrice.':'.$sim1.':'.$sim2.':'.$sim3.':'.$repack->BusinessType.':'.$rg->Volume.':'.$rg->VolumetricWeight.':'.$repack->InhouseRepackLbl.':'.$repack->ServiceId.':'.$repack->Status_Request.'">';
                              echo '</td>
                              <td width="100px">'.$rg->ItemName.'</td>
                              <td width="100px">'.$rg->ItemQuantity.'</td>
                              <td width="100px"><input type="hidden" name="ItemIdkTxt" value="'.$rg->ItemIdk.'"><input type="hidden" name="ItemQtyTxt" value="'.$rg->ItemQuantity.'"><input type="hidden" name="ItemQtyEdit" value="'.$rg->ItemQuantity.'">
                              <input type="text" class="form-control" name="txtQty" value="'.$rg->ItemQuantity.'" readonly ></td>
                              <td width="100px">'.$res->TrackingId.'</td>
                              <td width="100px">'.$res->MerchantName.'</td>'.$orderIdDis.$rmaValDis;
                              echo''.$mgtd.$grosswtTd.$declaredVal.'
                              <td width="100px"></td>
                              <td width="100px"></td>';
                              echo '</tr>';
                            }	
                        $idf++;	
                        	
                 }	
                 	
                 $i++;	
                  	
               }
                                
                       echo' </table></td><td style="display:none"></td><td style="display:none"></td>	
                        <td style="display:none"></td>	
                        <td style="display:none"></td>	
                        <td style="display:none"></td>	
                        <td style="display:none"></td>	
                        <td style="display:none"></td>	
                        <td style="display:none"></td>	
                        <td style="display:none"></td>	
                        <td style="display:none"></td>	
                        <td style="display:none"></td>';	
                        if($Gross_weight_display){
                            echo "<td style='display:none'></td>";
                         }
                         echo '</tr>';
                        
                         }	
                            }	
                        ?>
                     </tbody>
                     </table>
                  </div>
               </div>
            </div>


<div id="view_image" class="modal fade" role="dialog">
  <div class="modal-dialog modal-md">
    <div class="modal-content">
      <div class="modal-header">
          <input type="button" data-dismiss="modal" value="x" class="btn-close1">
        <h4 class="modal-title"><strong>Image View</strong></h4>
      </div>
      <div class="modal-body">
        <div class="row">
            
          <div class="col-md-12">
            <div id="viewImage">
                <div class="row" id="multimages">
                    
                </div>
                <div class="mult-img-container">
                <img id="expandedImg" src="" style="width:100%">
                <div id="imgalttext"></div>
                </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>


         </div>
      </div>
   </div>
</div>
<!-- Modal -->
<div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-hidden="true">
   <div class="modal-dialog">
      <div class="modal-content">
         <div class="modal-header">
            <input type="button" data-dismiss="modal" value="x" class="btn-close1">
            <h4 class="modal-title"><strong><?php echo $assArr['additional_address'];?></strong></h4>
         </div>
         <form name="userprofileFormSeven" id="userprofileFormSeven" method="post" action=""  enctype="multipart/form-data">
            <div class="modal-body pagshipup" style="display:none"><img src='/components/com_userprofile/images/loader.gif' height="400"></div>
            <div class="modal-body pagshipdown">
               <div class="row">
                  <div class="col-md-6">
                     <div class="form-group">
                        <label><?php echo  $assArr['type_of_user'];?> <span class="error">*</span></label>
                        <input type="text" class="form-control" name="typeuserTxt" id="typeuserTxt" readonly>
                     </div>
                  </div>
                  <div class="col-md-6">
                     <div class="form-group">
                        <label><?php echo $assArr['id'];?>  <span class="error">*</span></label>
                        <input type="text" class="form-control"  name="idTxt" id="idTxt" readonly>
                     </div>
                  </div>
               </div>
               <div class="row">
                  <div class="col-md-6">
                     <div class="form-group">
                        <label><?php echo $assArr['first_name'];?>  <span class="error">*</span></label>
                        <input type="text" class="form-control" name="fnameTxt" id="fnameTxt" maxlength="25">
                     </div>
                  </div>
                  <div class="col-md-6">
                     <div class="form-group">
                        <label><?php echo $assArr['last_Name'];?> <span class="error">*</span></label>
                        <input type="text" class="form-control"  name="lnameTxt" id="lnameTxt" maxlength="25">
                     </div>
                  </div>
               </div>
               <div class="row">
                  <div class="col-md-6">
                     <div class="form-group">
                        <label><?php echo $assArr['address_1'];?>  <span class="error">*</span></label>
                        <textarea type="text" class="form-control" name="addressTxt" id="addressTxt" maxlength="35"></textarea>
                     </div>
                  </div>
                  <div class="col-md-6">
                     <div class="form-group">
                        <label><?php echo $assArr['address_2'];?> 2 </label>
                        <textarea type="text" class="form-control" name="address2Txt" id="address2Txt" maxlength="35"></textarea>
                     </div>
                  </div>
               </div>
               <div class="row">
                  <div class="col-md-6">
                     <div class="form-group">
                        <label><?php echo $assArr['country'];?> <span class="error">*</span></label>
                        <?php
                           $countryView= UserprofileHelpersUserprofile::getCountriesList();
                           $arr = json_decode($countryView); 
                                          $countries='';
                           foreach($arr->Data as $rg){
                              $countries.= '<option value="'.$rg->CountryCode.'">'.$rg->CountryDesc.'</option>';
                                          }
                            
                           ?>
                        <select class="form-control" name="country2Txt" id="country2Txt">
                           <option value="0">Select Country</option>
                           <?php echo $countries;?>
                        </select>
                     </div>
                  </div>
                  <div class="col-md-6">
                     <div class="form-group">
                        <label><?php echo $assArr['state'];?> <span class="error">*</span></label>
                        <select class="form-control"  name="state2Txt" id="state2Txt">
                           <option value="">Select State</option>
                        </select>
                     </div>
                  </div>
               </div>
               <div class="row">
                  <div class="col-md-6">
                     <div class="form-group">
                        <label><?php echo $assArr['city'];?></label>
                        <!--<input type="text" class="form-control"  name="city2Txt" list="city2Txt" />-->
                        <!--<datalist id="city2Txt"></datalist>-->
                        <!--<input type="hidden" name="city2Txtdiv" id="city2Txtdiv">-->
                        <select  class="form-control"  name="city2Txt" id="city2Txt" autocomplete="off">
                           <option value=""><?php echo Jtext::_('COM_USERPROFILE_SELECT_CITY_LABEL');?></option>
                        </select>
                     </div>
                  </div>
                  <div class="col-md-6">
                     <div class="form-group">
                        <label><?php echo $assArr['zip_code'];?> </label>
                        <input type="text" class="form-control" name="zipTxt" id="zipTxt">
                     </div>
                  </div>
               </div>
             
              <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label><?php echo $assArr['identification_type'];?><span class="error">*</span></label>
                        <?php
                                    $idtypeView= Controlbox::getidentityList();
                                    $arr = json_decode($idtypeView); 
                                    $idtypeformfive='';
                                    foreach($arr as $rg){
                                     $idtypeformfive.= '<option value="'.$rg->id_values.'">'.$rg->id_values.'</option>';
                                    } 
                                    ?>
                                 <select class="form-control" id="idtypeTxt" name="idtypeTxt">
                                    <option value=""><?php echo Jtext::_('COM_REGISTER_SELECT_IDENTITY_LABEL');?></option>
                                    <?php echo $idtypeformfive;?>
                                 </select>
                    </div>
                </div>    
                <div class="col-md-6">
                    <div class="form-group">
                                 <label><?php echo $assArr['identification_value'];?><span class="error">*</span></label>
                                 <input type="text" class="form-control" name="idvalueTxt" id="idvalueTxt">
                              </div>
                </div>        
          </div>
             
               <div class="row">
                  <div class="col-md-6">
                     <div class="form-group">
                        <label><?php echo $assArr['email'];?> <span class="error">*</span></label>
                        <input type="text" class="form-control" name="emailTxt" id="emailTxt">
                     </div>
                  </div>
               </div>
               <div class="row">
                  <div class="col-md-12 text-center">
                     <input type="button" value="<?php echo $assArr['save']?>" class="btn btn-primary">
                     <input type="button" value="<?php echo $assArr['cancel'];?>" data-dismiss="modal" class="btn btn-danger">
                  </div>
               </div>
            </div>
         </form>
      </div>
   </div>
</div>
<!-- Modal -->
<!--<div id="ship_all" class="modal fade" role="dialog">-->
<!--   <div class="modal-dialog modal-sm">-->
<!--      <div class="modal-content">-->
<!--         <div class="modal-header">-->
<!--            <h4 class="modal-title"><strong>Confirmation</strong></h4>-->
<!--         </div>-->
<!--         <div class="modal-body">-->
<!--            <div class="row">-->
<!--               <div class="col-sm-12">-->
<!--                  <div class="form-group">-->
<!--                     <p id="error"></p>-->
<!--                     <label id="confirmq">Do you want to ship all ?</label>-->
<!--                  </div>-->
<!--               </div>-->
<!--            </div>-->
<!--            <div class="row">-->
<!--               <div class="col-sm-12 text-center">-->
<!--                  <input type="button" value="OK" class="btn btn-primary shipAll">-->
<!--                  <input type="button" value="Close" data-dismiss="modal" class="btn btn-danger">-->
<!--               </div>-->
<!--            </div>-->
<!--         </div>-->
<!--      </div>-->
<!--   </div>-->
<!--</div>-->


<script src="https://code.jquery.com/jquery-3.5.1.min.js" integrity="sha256-9/aliU8dGd2tb6OSsuzixeV4y/faTqgFtohetphbbj0=" crossorigin="anonymous"></script>

<script type="text/javascript">
//   // Create and initialize a payment form object
//   const paymentForm = new SqPaymentForm({
//      // Initialize the payment form elements
     
//      //TODO: Replace with your sandbox application ID sandbox-sq0idb-n0mRDDVFmPIFGl8A7410zg
//      //applicationId: "sandbox-sq0idb-Y-iff9zZeN6J1DXEqNYA9Q",
//      applicationId: "sandbox-sq0idb-n0mRDDVFmPIFGl8A7410zg",
//      inputClass: 'sq-input',
//      autoBuild: false,
//      // Customize the CSS for SqPaymentForm iframe elements
//      inputStyles: [{
//          fontSize: '16px',
//          lineHeight: '24px',
//          padding: '8px 10px',
//          placeholderColor: '#a0a0a0',
//          backgroundColor: 'transparent',
//      }],
//      // Initialize the credit card placeholders
//      cardNumber: {
//          elementId: 'sq-card-number',
//          placeholder: '4111111111111111'
//      },
//      cvv: {
//          elementId: 'sq-cvv',
//          placeholder: '111'
//      },
//      expirationDate: {
//          elementId: 'sq-expiration-date',
//          placeholder: '12/21'
//      },
//      postalCode: {
//          elementId: 'sq-postal-code',
//          placeholder: '11111'
//      },
//      // SqPaymentForm callback functions
//      callbacks: {
//          /*
//          * callback function: cardNonceResponseReceived
//          * Triggered when: SqPaymentForm completes a card nonce request
//          */
//          cardNonceResponseReceived: function (errors, nonce, cardData) {
//          if (errors) {
//              alert("Please fill all required fields");
//              // Log errors from nonce generation to the browser developer console.
//              console.error('Encountered errors:');
//              errors.forEach(function (error) {
//                  console.error('  ' + error.message);
//              });
//              //alert('Encountered errors, check browser developer console for more details');
//              return;
//          }
         
         
//           document.getElementById('card-nonce').value = nonce;
         
//           document.getElementById('userprofileFormFive').submit();
         
//          }
//      }
//   });
   
    // paymentForm.build();
    
   // onGetCardNonce is triggered when the "Pay $1.00" button is clicked
//   function onGetCardNonce(event) {
//       event.preventDefault();
//       if($joomla('input[name="cc"]').is(":visible") == true){
//           if($joomla('input[name="cc"]').is(":checked") == false){
//               alert("Please check any one payment option");
//               return false;
//           }
//       }
       
//   if($joomla('input[name="cc"]:checked').val() == "Paypal"){
       
//       $joomla(".page_loader").show();
//       var articlestrs=[];
//           $joomla.each($joomla("input[name='articleStr[]']"), function(){
//               articlestrs.push($joomla(this).val());
//           });    
//           var pricestrs=[];
//           $joomla.each($joomla("input[name='priceStr[]']"), function(){
//               pricestrs.push($joomla(this).val());
//           });    
//          $joomla('#shipcostStr').val($joomla('#amount').val());
//          var user="<#php echo $user;?>";
//          $joomla('input[name="data_test"]').val($joomla('input[name="invidkStr"]').val()+":"+$joomla('[name="wherhourecStr"]').val()+":"+$joomla('[name="consignidStr"]').val()+":"+$joomla('input[name="txtspecialinsStr"]').val()+":"+$joomla('input[name="shipservtStr"]').val()+":"+$joomla('input[name="paypalinvoice"]').val()+":"+articlestrs+":"+pricestrs+":"+$joomla('input[name="Conveniencefees"]').val()+":"+$joomla('input[name="addSerStr"]').val()+":"+$joomla('input[name="addSerCostStr"]').val()+":"+$joomla('input[name="ratetypeStr"]').val()+":"+$joomla('input[name="companyId"]').val()+":"+$joomla('input[name="insuranceCost"]').val()+":"+$joomla('input[name="extAddSer"]').val()+":"+$joomla("#lengthStr").val()+":"+$joomla("#widthStr").val()+":"+$joomla("#heightStr").val()+":"+$joomla("#weightStr").val()+":"+$joomla("#volStr").val()+":"+$joomla("#volmetStr").val()+":"+$joomla('#totalDecVal').val()+":"+$joomla('input[name=shipmentCost]').val()+":"+$joomla('#couponCodeStr').val()+":"+$joomla('input[name=couponDiscAmt]').val()+":"+user);
//          $joomla('input[name="item_name"]').val(''+":"+user);
//           var sdf=0;
//           $joomla("input[name='priceStr[]']").each( function () {
//               if($joomla(this).val()=="0.00"){  
//                   sdf=1;
//                   $joomla(".page_loader").hide();
//                   return false;
//               }    
//           });    
//           if(sdf == 0){
              
//                   $joomla.ajax({
//                       	url: "<?#php echo JURI::base(); ?>index.php?option=com_userprofile&task=user.get_ajax_data&jpath=<?php echo urlencode  (JPATH_SITE); ?>&pseudoParam="+new Date().getTime(),
//                   	    dataType: 'text',  
//                           data: {"inscustdataflag":1,"item_name":$joomla('input[name="data_test"]').val(),"item_number":$joomla('input[name="item_number"]').val(),"user":"<?php echo $user;?>"},                         
//                           type: 'post',
//                       	beforeSend: function() {
                             
//                           },success: function(data){
//                              $joomla('input[name="data_test"]').val("");
//                              document.getElementById('userprofileFormFive').submit();
//                           }
//                      });
                     
              
           
//           }
           
//   }else{
//       $joomla(".page_loader").show();
//       document.getElementById('userprofileFormFive').submit();
//   }
   
//   }
     
     
</script>
<script src="<?php echo JUri::base(true); ?>/components/com_userprofile/assets/js/jquery.payform.min.js" charset="utf-8"></script>
<script src="<?php echo JUri::base(true); ?>/components/com_userprofile/assets/js/script.js"></script>
<script>
// $joomla(document).keyup('#cvv,#cardNumber',function() {
//         $joomla("#cvv-error").html("");
//         cardNum = $joomla('#cardNumber').val();
//         cardNumLen = cardNum.length;
//         if(cardNumLen == 16){
//             $joomla("#cvv").attr("maxlength",3);
//             $joomla("#cvv").attr("minlength",3);
//             $joomla("#cvv-error").html("<?#php echo Jtext::_('COM_USERPROFILE_ENTER_THREE_DIGITS_ERROR'); ?>");
//         }else if(cardNumLen == 15){
//             $joomla("#cvv").attr("maxlength",4);
//             $joomla("#cvv").attr("minlength",4);
//             $joomla("#cvv-error").html("<?#php echo Jtext::_('COM_USERPROFILE_ENTER_FOUR_DIGITS_ERROR'); ?>");
//         }else{
//             $joomla("#cvv").attr("maxlength",3);
//             $joomla("#cvv").attr("minlength",3);
//             $joomla("#cvv-error").html("<?#php echo Jtext::_('COM_USERPROFILE_ENTER_THREE_DIGITS_ERROR'); ?>");
//         }
//     });
//     $joomla("#cvv,#cardNumber").on('blur',function() {
       
//         $joomla("#cvv-error").html("");
//         cardNum = $joomla('#cardNumber').val();
//         cardNumLen = cardNum.length;
//         if(cardNumLen == 16){
//             $joomla("#cvv").attr("maxlength",3);
//             $joomla("#cvv").attr("minlength",3);
//             $joomla("#cvv-error").html("<?#php echo Jtext::_('COM_USERPROFILE_ENTER_THREE_DIGITS_ERROR'); ?>");
//         }else if(cardNumLen == 15){
//             $joomla("#cvv").attr("maxlength",4);
//             $joomla("#cvv").attr("minlength",4);
//             $joomla("#cvv-error").html("<?#php echo Jtext::_('COM_USERPROFILE_ENTER_FOUR_DIGITS_ERROR'); ?>");
//         }else{
//             $joomla("#cvv").attr("maxlength",3);
//             $joomla("#cvv").attr("minlength",3);
//             $joomla("#cvv-error").html("<?#php echo Jtext::_('COM_USERPROFILE_ENTER_THREE_DIGITS_ERROR'); ?>");
//         }
//     });

    // repack

//     $joomla(document).on('click','#repack,#consolidation',function() {
        
//               var requrl="<?#php echo JURI::base(); ?>index.php?option=com_userprofile&task=user.get_ajax_data&jpath=<?#php echo urlencode  (JPATH_SITE); ?>&pseudoParam="+new Date().getTime();
//               var statusRequest = $joomla(this).attr('id');
//               var shipval = $joomla('.txtId:checked').val();
//               var txtIdVals= shipval.split(":");
//               var custSer = txtIdVals[32];
//               var repStatus= txtIdVals[33];
               
//               wrhStr = $joomla("#wherhourecStr").val();
//               invidkStr = $joomla("#invidkStr").val();
//               qtyStr = $joomla("#qtyStr").val();
//               repackComments = $joomla("#repackDesc").val();
              

//               var articleStrs=[];
//               $joomla.each($joomla("input[name='articleStr[]']"), function(){
//                   articleStrs.push($joomla(this).val());
//               });    
//               var priceStrs=[];
//               $joomla.each($joomla("input[name='priceStr[]']"), function(){
//                   priceStrs.push($joomla(this).val());
//               }); 
               
//               repackLblStr = $joomla("#repackLblStr").val();
//               repackArr = repackLblStr.split(",");
               
             
//               if(repackArr.length == 1 && (repStatus == "Repack" || repStatus == "Consolidation")){
//                   if(repStatus == "Repack"){
//                       repackConsolidateMsg = "This item was already repacked. You can't repack/consolidate again."
//                   }else{
//                       repackConsolidateMsg = "This item was already consolidated. You can't repack/consolidate again."
//                   }
                   
//                   displayWarningAlert(repackConsolidateMsg);
                   
//                   $joomla("#ord_repack").modal('hide');
//                   $joomla("#ord_consolid").modal('hide');
//                   $joomla(".check_all_items_repack").prop( "checked", false );
                 
//                   return false;
//               }else{
             
//                         $joomla.ajax({
//                           url: requrl,
//                           data: { "repackflag":1,"wrhStr":wrhStr,"invidkStr":invidkStr,"qtyStr":qtyStr,"articlestrs":articleStrs,"pricestrs":priceStrs,"statusRequest":statusRequest,"repackComments":repackComments,"user":"<?php echo $user; ?>","custSer":custSer,"repackLblStr":repackLblStr},
//                           dataType:"html",
//                           type: "get",
//                                 beforeSend: function() {
                                 
//                                   $joomla("#ord_repack").modal('hide');
//                                   $joomla("#ord_consolid").modal('hide');
//                                   $joomla(".page_loader").show();
//                                 },
//                                 success: function(data){
//                                  var res = data.split(":");
                        
//                                  if(parseFloat(res[0]) !=0){
//                                     localStorage.setItem("repackConsolidStatus",res[1]);
//                                     window.location.reload();
//                                  }else{
//                                     $joomla(".page_loader").hide();
//                                     displayWarningAlert(res[1]);
                                    
//                                  }
                                
//                               }
//                             }); 

//                 }
  
//  });

// end

// unpack  or deconsolidation

// $joomla(document).on('click','.itemunpack',function() {
//     $joomla(".unpackError").html("");
//     var wrhsList = $joomla(this).attr('data-val');
//     var wrhsListArr = wrhsList.split(",");
//     $joomla("#repackLblStr").val($joomla(this).attr('data-id'));
    
//     wrhsListStr = '';
//     for(i=0;i<wrhsListArr.length;i++){
//         wrhsListStr += '<div><input type="checkbox" checked class="unpackwrhs" data-val="'+wrhsListArr[i]+'" ><label>'+wrhsListArr[i]+'</label></div>';
//     }
    
//     $joomla(".wrhsListDiv").html(wrhsListStr);
    
//     var statusName = $joomla(this).attr("name");
//       $joomla("#ord_unpack").find('input[type=submit]').attr("id",statusName);
//       $joomla("#ord_unpack").modal('show');
       
   
   
// });

// $joomla(document).on('click','#unpack,#deconsolidation',function() {
//   var repackId = '';
//   var wrhs='';
//   var statusRequest = $joomla(this).attr('id');
//   var repackId = $joomla("#repackLblStr").val();
   
//       $joomla('.txtId:checked').each(function(){
//           var shipval = $joomla(this).val();
//           var txtIdVals= shipval.split(":");
//           });
          
//          $joomla(".unpackwrhs:checked").each(function(){
//              wrhs += $joomla(this).attr("data-val")+",";
//          });
         
//          wrhs = wrhs.replace(/^\s+|\,+$/gm,'');
//          wrhsArr = wrhs.split(",");
        
//          if(wrhsArr.length > 0 && wrhsArr[0] !=''){
//               //var wrhs = $joomla("#wherhourecStr").val();
//               var requrl="<?#php echo JURI::base(); ?>index.php?option=com_userprofile&task=user.get_ajax_data&jpath=<?#php echo urlencode  (JPATH_SITE); ?>&pseudoParam="+new Date().getTime();
        
//               $joomla.ajax({
//               url: requrl,
//               data: { "unpackflag":1,"wrhs":wrhs,"repackId":repackId,"StatusRequest":statusRequest},
//               dataType:"html",
//               type: "get",
//                     beforeSend: function() {
//                       $joomla("#ord_unpack").modal('hide');
//                       $joomla(".page_loader").show();
//                     },
//                     success: function(data){
//                       var res = data.split(":");
//                       if(res[0]){
//                           //alert(res[1]);
//                           localStorage.setItem("unpackSuccess",res[1]);
//                           window.location.reload();
//                       }
//                     }
//               });  
//          }else{
//               $joomla(".unpackError").html("Please select checkbox");
//          }
  

   
// });



// $joomla(document).on('click','.whrse-link',function() {
//     $joomla(".wrhsDetBody").html("");
//     var wrhsno = $joomla(this).text();
    
    
//         var requrl="<?#php echo JURI::base(); ?>index.php?option=com_userprofile&task=user.get_ajax_data&jpath=<?#php echo urlencode  (JPATH_SITE); ?>&pseudoParam="+new Date().getTime();
    
//     $joomla.ajax({
//           url: requrl,
//           data: { "wrhsdetflag":1,"user":"<?#php echo $user; ?>","wrhsno":wrhsno},
//           dataType:"html",
//           type: "get",
//                 beforeSend: function() {
//                   $joomla(".page_loader").show();
//                 },
//                 success: function(data){
//                     $joomla(".page_loader").hide();
//                     $joomla(".wrhsDetBody").html(data);
//                     $joomla("#wrhsDetails").modal("show");
                 
//                 }
//           });  
    

// });

// $joomla(document).on('click','.repack-link',function() {
//     $joomla(".wrhsDetBody").html("");
//     var wrhsno = $joomla(this).attr("data-val");
    
//         var requrl="<?#php echo JURI::base(); ?>index.php?option=com_userprofile&task=user.get_ajax_data&jpath=<?php echo urlencode  (JPATH_SITE); ?>&pseudoParam="+new Date().getTime();
    
//     $joomla.ajax({
//           url: requrl,
//           data: { "wrhsdetflag":1,"user":"<?#php echo $user; ?>","wrhsno":wrhsno},
//           dataType:"html",
//           type: "get",
//                 beforeSend: function() {
//                   $joomla(".page_loader").show();
//                 },
//                 success: function(data){
//                     $joomla(".page_loader").hide();
//                     $joomla(".wrhsDetBody").html(data);
//                     $joomla("#wrhsDetails").modal("show");
                 
//                 }
//           });
// });

// $joomla(document).on('click','#repack_btn,#consolid_btn',function() {
//             $joomla("#repackDesc").val("");
//             var checkboxLen = $joomla("input[name='txtId']").filter(":checked").length;
//             if(checkboxLen){
                
//               var btnVal = $joomla(this).val();
//               if(btnVal == 'repack'){
//                   $joomla("#ord_repack").modal("show");
//               }else{
//                   $joomla("#ord_consolid").modal("show");
//               }
            
//             }else{
//                 alert("Please select at least one item");
//             }
// });

 


    
    
</script>

 <!--paypal credit / debit card payment integration Start-->
<script src="<?php echo JUri::base(true); ?>/components/com_userprofile/js/custom.js"></script>  
<!--<script>-->
<!--$joomla(document).ready(function() {-->
<!--    if(clientId !=""){-->
<!--          initPayPalButton();-->
<!--    }-->
    
<!--});-->
<!--</script>-->
 
<!--<style>-->
<!--   #divShipCOstOne input{margin-right:5px;}-->
<!--   #divShipCOstOne{clear:both;}-->
<!--   .third {-->
   <!--/*float: left;,width: calc((100% - 32px) / 3);*/-->
<!--   padding: 0;-->

<!--   margin: 0 16px 16px 0;-->
<!--   }-->
<!--   .third:last-of-type {-->
<!--   margin-right: 0;-->
<!--   }-->
   <!--/* Define how SqPaymentForm iframes should look */-->
<!--   .sq-input {-->
<!--   height:40px;-->
<!--   box-sizing: border-box;-->
<!--   border: 1px solid #404040;-->
<!--   border-radius: 4px;-->
<!--   outline-offset: -2px;-->
<!--   display: inline-block;-->
<!--   -webkit-transition: border-color .2s ease-in-out, background .2s ease-in-out;-->
<!--   -moz-transition: border-color .2s ease-in-out, background .2s ease-in-out;-->
<!--   -ms-transition: border-color .2s ease-in-out, background .2s ease-in-out;-->
<!--   transition: border-color .2s ease-in-out, background .2s ease-in-out;-->
<!--   }-->
   <!--/* Define how SqPaymentForm iframes should look when they have focus */-->
<!--   .sq-input--focus {-->
<!--   border: 1px solid #4A90E2;-->
<!--   background-color: rgba(74,144,226,0.02);-->
<!--   }-->
   <!--/* Define how SqPaymentForm iframes should look when they contain invalid values */-->
<!--   .sq-input--error {-->
<!--   border: 1px solid #E02F2F;-->
<!--   background-color: rgba(244,47,47,0.02);-->
<!--   }-->
<!--   #sq-card-number {-->
<!--   margin-bottom: 16px;-->
<!--   }-->
<!--   .source_id{-->
<!--   color : green;-->
<!--   }-->
<!--</style>-->

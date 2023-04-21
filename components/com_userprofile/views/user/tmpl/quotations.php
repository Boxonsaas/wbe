<?php
/**
 * @version    CVS: 1.0.0
 * @package    Com_Userprofile
 * @author     madan <madanchunchu@gmail.com>
 * @copyright  2018 madan
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
// No direct access
defined('_JEXEC') or die;
$document = JFactory::getDocument();
$document->setTitle("Quotation in Boxon Pobox Software");
$session = JFactory::getSession();
$user=$session->get('user_casillero_id');
$pass=$session->get('user_casillero_password');

if(!$user){
    $app =& JFactory::getApplication();
    $app->redirect('index.php?option=com_register&view=login');
}
$resWp=UserprofileHelpersUserprofile::getQuotationFieldviewsList($user);
//dynamic elements

$res = Controlbox::dynamicElements('Quotation');
   $elem=array();
   foreach($res as $element){
      $elem[$element->ElementId]=array($element->ElementDescription,$element->ElementStatus,$element->is_mandatory,$element->is_default,$element->ElementValue);
   }

// get cust type and menu access

$menuAccessStr=Controlbox::getMenuAccess($user,$pass);
$menuCustData = explode(":",$menuAccessStr);
$maccarr=array();
foreach($menuCustData as $menuaccess){
    
    $macess = explode(",",$menuaccess);
    $maccarr[$macess[0]]=$macess[1];
 
}
$menuCustType=end($menuCustData);

// get labels
    $lang=$session->get('lang_sel');
    $res=Controlbox::getlabels($lang);
    $assArr = [];
    
    foreach($res->data as $response){
    $assArr[$response->id]  = $response->text;
    }


// end


// echo '<pre>';
// var_dump($resWp->Commodity_List);exit;

//https://cdn.jsdelivr.net/jquery.validation/1.15.1
?>

<?php include 'dasboard_navigation.php' ?>
<script type="text/javascript" src="<?php echo JUri::base(true); ?>/components/com_userprofile/js/jquery.validate.min.js"></script> 

<script type="text/javascript">
var $joomla = jQuery.noConflict(); 
$joomla(document).ready(function(){
        history.pushState(null, null, location.href);
    window.onpopstate = function () {
        history.go(1);
    };
    
    
  // dynamic service types on source, destination and shiptype change
    
   $joomla('select[name=txtSourceCntry],select[name=txtDestinationCntry],select[name=txtTypeOfShipperName]').on('change',function(){
         
            var shipmentType = $joomla('select[name=txtTypeOfShipperName]').val();
            var srcCountry = $joomla('#txtSourceCntry').val(); 
            var destCountry = $joomla('#txtDestinationCntry').val();
            
            var urls="<?php echo JURI::base(); ?>index.php?option=com_userprofile&task=user.get_ajax_data&destination="+ destCountry +"&source="+ srcCountry +"&shiptype="+ shipmentType +"&user=<?php echo $user;?>&jpath=<?php echo urlencode  (JPATH_SITE); ?>&pseudoParam="+new Date().getTime();
            
            $joomla.ajax({
            	url: urls,
            	data: { "shippmenttypecalcflag": '1' },
            	dataType:"html",
            	type: "get",
                beforeSend: function() {
                    $joomla(".page_loader").show();
                },
                success: function(data){
                    $joomla(".page_loader").hide();
                  $joomla('select[name=txtServiceType]').html(data);
              }
            }); 
        
    });

    
    $joomla('select[name="txtTypeOfShipperName"]').change(function(){
        $joomla('select[name="txtServiceType"]').empty();
        var resServ='<?php foreach($resWp->ServiceType_List as $key=>$row){echo '<option value="'.$row->id_values.'">'.$row->desc_vals.'</option>';}?>';
        if($joomla(this).val()=="")
        $joomla('select[name="txtServiceType"]').append('<option value="">Select</option>');
        else
        $joomla('select[name="txtServiceType"]').append('<option value="">Select</option>'+resServ);
    });
    
    $joomla('#gtable').on('change','select[name="txtPackageList[]"]',function(e){
        e.preventDefault();
        var ths=$joomla(this).closest('tr').parent().index();
        var resVal=$joomla(this).val();
        resVal=resVal.split(":");
        if(resVal !='')
        $joomla.ajax({
			url: "<?php echo JURI::base(); ?>index.php?option=com_userprofile&task=user.get_ajax_data&getpackagetype="+resVal[0] +"&getpackageflag=1&jpath=<?php echo urlencode  (JPATH_SITE); ?>&pseudoParam="+new Date().getTime(),
			data: { "getpackagetype": $joomla(this).data('id') },
			dataType:"html",
			type: "get",
			beforeSend: function() {
              $joomla(".page_loader").show();
              $joomla(":button").attr("disabled", true);
              $joomla(':input[type="submit"]').prop('disabled', true);
           },success: function(data){
              $joomla(":button").attr("disabled", false); 
              //$joomla(':input[type="submit"]').prop('disabled', false);
              
              $joomla(".page_loader").hide();
              var cospor=data;

		      cospor=cospor.split(":");
		      console.log(ths);
		      $joomla('#gtable tr').eq(ths).find('#txtLength').val(cospor[0]);
		      $joomla('#gtable tr').eq(ths).find('#txtWidth').val(cospor[1]);
		      $joomla('#gtable tr').eq(ths).find('#txtHeight').val(cospor[2]);
              $joomla('#btnCalculate').removeAttr("disabled");
            }
		});
    }); 
    
    $joomla('#btnCalculate').attr("disabled", "disabled");
    $joomla('#gtable').on('click','input[name="addrow"]',function(e){
      if($joomla("form[name='userprofileFormOne']").valid()==true){  
          var iteid=$joomla(this).closest('tr').find('td:first input').attr('id');
          var comid=$joomla(this).closest('tr').find('td:nth-child(3) select').attr('id');
          var lenid=$joomla(this).closest('tr').find('td:nth-child(5) input').attr('id');
          var widid=$joomla(this).closest('tr').find('td:nth-child(6) input').attr('id');
          var heiid=$joomla(this).closest('tr').find('td:nth-child(7) input').attr('id');
          
          var ip=$joomla(this).closest('tr').find('td:first input').attr('id');
          var ir=ip+4;
          var rp=$joomla(this).closest('tr').find('td:nth-child(2) select').attr('id');
          var er=rp+1;
          var rrp=$joomla(this).closest('tr').find('td:nth-child(4) input').attr('id');
          var rer=rrp+2;
          var rrrp=$joomla(this).closest('tr').find('td:nth-child(8) input').attr('id');
          var rrer=rrrp+3;
          var text = '';
          text=$joomla(this).closest('tr').html().replace('id="'+ip+'"','id="'+ir+'"').replace('id="'+rp+'"','id="'+er+'"').replace('id="'+rrp+'"','id="'+rer+'"').replace('id="'+rrrp+'"','id="'+rrer+'"');
          $joomla(this).closest('tr').find('td:last').html('<input class="title btn btn-danger btn-rem" type="button" name="deleterow" value="X">');
          
          $joomla("#gtable").append('<tbody><tr>'+text+'</tr></tbody>');
          
          var pkg=$joomla('#'+rp).val();
          $joomla("#gtable").find('tr:last td:nth-child(2) select').val(pkg);
          
          var commid=$joomla('#'+comid).val();
          $joomla("#gtable").find('tr:last td:nth-child(3) select').val(commid);
          
          var qt=$joomla('#'+rrp).val();
          $joomla("#gtable").find('tr:last td:nth-child(4) input').val(qt);
          
          var legnid=$joomla('#'+lenid).val();
          $joomla("#gtable").find('tr:last td:nth-child(5) input').val(legnid);
          var widtid=$joomla('#'+widid).val();
          $joomla("#gtable").find('tr:last td:nth-child(6) input').val(widtid);
          
          var heitid=$joomla('#'+heiid).val();
          $joomla("#gtable").find('tr:last td:nth-child(7) input').val(heitid);
          
          var gsw=$joomla('#'+rrrp).val();
          $joomla("#gtable").find('tr:last td:nth-child(8) input').val(gsw);
      }
    });    
    $joomla('#gtable').on('click','input[name="deleterow"]',function(e){
      if($joomla('#gtable tr').length>2){    
        var lastone=$joomla('#gtable tr:last td:last').html();
        $joomla(this).closest('tbody').remove();
        $joomla('#gtable tr:last td:last').html('');
        $joomla('#gtable tr:last td:last').html(lastone);
     }    
      else
      alert('Minimum One Row Required');
    });
    
    $joomla(".form-control").change(function() { 
        $joomla('input[name=txtquotationCost]').val('');
    });
    
    $joomla('#btnCalculate').click(function(e){
        e.preventDefault();

        validation();
        if($joomla("form[name='userprofileFormOne']").valid()==true ){
        
        $joomla.ajax({
            url: "<?php echo JURI::base(); ?>index.php?option=com_userprofile&task=user.get_ajax_data&getcalculatetype=1&userid=<?php echo $user;?>&munits="+$joomla('select[name="txtMeasurementUnits"]').val() +"&tos="+$joomla('select[name="txtTypeOfShipperName"]').val() +"&stype="+$joomla('select[name="txtServiceType"]').val() +"&source="+$joomla('select[name="txtSourceCntry"]').val() +"&dt="+$joomla('select[name="txtDestinationCntry"]').val() +"&length="+$joomla('input[name="txtLengthIds"]').val() +"&width="+$joomla('input[name="txtWidthIds"]').val() +"&height="+$joomla('input[name="txtHeightIds"]').val() +"&qty="+$joomla('input[name="txtQuantityIds"]').val() +"&gwt="+$joomla('input[name="txtWeightIds"]').val() +"&wtunits="+$joomla('select[name="txtWeightUnits"]').val()+"&bustype="+$joomla('select[name="txtBusinessType"]').val()+"&getcalculateflag=1&jpath=<?php echo urlencode  (JPATH_SITE); ?>&pseudoParam="+new Date().getTime(),
            data: { "getpackagetype": $joomla(this).data('id') },
            dataType:"html",
            type: "get",
            beforeSend: function() {
              $joomla(".page_loader").show();
              $joomla(":button").attr("disabled", true); 
              $joomla(':input[type="submit"]').prop('disabled', true);
            },success: function(data){
                 
              $joomla(":button").attr("disabled", false); 
              console.log(data);
              $joomla(".page_loader").hide(); 
              var cospor=data;
              cospor=cospor.split(":");
              $joomla('#divApiResult').html('');
              $joomla('#divquotationCost').html('');
           
              if(cospor[3]>0){
                 $joomla(':input[type="submit"]').prop('disabled', false); 
                 $joomla('input[name=txtRatetypeIds]').val(cospor[0]);
                 //console.log("Valuem:"+cospor[1]);
                 var gw=cospor[1];
                 gw=gw.split(",");
                 $joomla.each( gw, function( key, value ) {
                    var ift=key+1;
                    var subStr = parseFloat(value);
                    //var subStr = value.substring(0, 4);
                    $joomla('#gtable tr').eq(ift).find('#divVolumeMultiple').html(subStr.toFixed(2));
                 });
                 $joomla('input[name=txtVolumeMultiple]').val(cospor[1]);
                 var vw=cospor[2];
                 vw=vw.split(",");
                 $joomla.each( vw, function( key, value ) {
                    var vift=key+1;
                    var subStrs = parseFloat(value);
                    //var subStrs = value.substring(0, 5);
                    $joomla('#gtable tr').eq(vift).find('#divVolWtMultiple').html(subStrs.toFixed(2));
                 });
                 $joomla('input[name=txtVolWtMultiple]').val(cospor[2]);
                 
                 $joomla('#divquotationCost').html('');
                 $joomla('#divquotationCost').html('$' + cospor[3]);
                 $joomla('input[name=txtIdServ]').val(cospor[4]);
                 $joomla('input[name=txtIdRate]').val(cospor[5]);
                 $joomla('#divdiscountCost').html('');
                 console.log(typeof(cospor[6]));
                 //$joomla('#divdiscountCost').html(cospor[6]);
                 if(cospor[6]==""){
                     cospor[6]="0.00";
                 }
                  $joomla('#divdiscountCost').html('$'+cospor[6]);
                  $joomla('input[name=txtDiscount]').val(cospor[6]);
                     
                 var quotcost=0;
                 quotcost=cospor[3];
                 $joomla('input[name=txtquotationCost]').val(quotcost);

                 var adds=0;
                 adds=cospor[7];
                 adds = adds.split(',');
                 var sum = 0;
                 for(j=0;j<adds.length;j++){
                   console.log(parseFloat(adds[j]));
                   if(isNaN(parseFloat(adds[j])))
                   sum += 0;
                   else
                   sum += parseFloat(adds[j]);
                 }
                 if(isNaN(sum)){
                   sum="0.00";
                 }
                 sum=sum.toFixed(2);
                 $joomla('input[name=txtAdditionalServices]').val(cospor[7]);
                 $joomla('#divadditionalCost').html('');
                 $joomla('#divadditionalCost').html('$'+sum);                
                 
                 var dgw=cospor[8];
                 dgw=dgw.split(",");
                 $joomla.each( dgw, function( key, value ) {
                    var vdgw=key+1;
                    $joomla('#gtable tr').eq(vdgw).find('#divGrossWeight').html(value);
                 });
                 $joomla('input[name=txtGrossWeight]').val(cospor[8]);
                 $joomla('#divfinalCost').html('');
                 var fico=0;
                 fico=parseFloat(quotcost)+parseFloat(sum);
                 
                 if(cospor[6]>0){
                     fico=parseFloat(fico)-parseFloat(cospor[6]);
                     $joomla('input[name=txtfinalCost]').val(fico);
                 }else{
                     $joomla('input[name=txtDiscount]').val(0);

                 }
                 
                 
                 if(fico!="NaN"){
                    $joomla('#divfinalCost').html('$'+fico.toFixed(2));
                    $joomla('input[name=txtfinalCost]').val(fico);
                 }
                 $joomla('input[name=txtAdditionalServicesId]').val(cospor[9]);
                 
              }else{
                 
                $joomla('#divApiResult').html('<div class="col-sm-12"><label class="error">'+data+'</label></div>');
                 $joomla('#divadditionalCost').html('');
                 $joomla('#divdiscountCost').html('');
                 $joomla('#divfinalCost').html('');  
                
              }
              console.log(data);
              return false;
            }
		});
        }else{
           //$joomla('input[name="submit"]').click();
        }
    });    
     
    
   // Wait for the DOM to be ready
    $joomla(function() {
        
        // Initialize form validation on the registration form.
        // It has the name attribute "registration"
        $joomla("form[name='userprofileFormOne']").validate({
        
        // Specify validation rules
        rules: {
          // The key name on the left side is the name attribute
          // of an input field. Validation rules are defined
          // on the right side
          txtTypeOfShipperName: {
            required: true
          },
          txtServiceType: {
            required: true
          },
          txtSourceCntry: {
            required: true
          },
          txtDestinationCntry: {
            required: true
          },
          txtMeasurementUnits: {
            required: true
          },
          txtWeightUnits: {
            required: true
         },
         txtNotes:{
            required: function(){ 
                if($joomla("input[name='txtIns']:checked").val())
                {
                    return true;
                    
                }else{ 
                    return false;
                    
                }
            }
         },
         "txtItemName[]": "required",
         "txtPackageList[]": "required",
         "txtQuantity[]": "required",
         "txtWeight[]": "required"


        },
        // Specify validation error messages
        messages: {
          txtTypeOfShipperName: "<?php echo $assArr['type_Of_Shipment_error']; ?>",
          txtServiceType: "<?php echo $assArr['service_Type_error'];  ?>",
          txtSourceCntry: "<?php echo $assArr['source_error'];  ?>",
          txtDestinationCntry: "<?php echo $assArr['destination_country_error'];  ?>",
          txtMeasurementUnits: "<?php echo $assArr['measurement_Units_error']; ?>",
          txtWeightUnits: "<?php echo $assArr['weight_Units_error'];  ?>",
          txtLength: "<?php echo $assArr['length-error']; ?>",
          txtWidth: "<?php echo $assArr['width_error']; ?>",
          txtHeight: "<?php echo $assArr['height_error']; ?>",
          txtValuemetric: "Please enter item Length",
        //   txtNotes:"Please enter Notes",
          "txtItemName[]": "<?php echo $assArr['item_name_error']; ?>",
          "txtPackageList[]": "<?php echo Jtext::_("COM_USERPROFILE_QUOTATION_SELECT_PACKAGE")  ?>",
          "txtQuantity[]": "<?php echo $assArr['quAntity_error']; ?>",
          "txtWeight[]": "<?php echo Jtext::_("COM_USERPROFILE_QUOTATION_SELECT_WEIGHT")  ?>"
          
        },
        // Make sure the form is submitted to the destination defined
        // in the "action" attribute of the form when valid
        submitHandler: function(form) {
            $joomla(".page_loader").show();
            if($joomla('input[name=txtquotationCost]').val()==0)
            {
                validation();
                var testing = false;
                $joomla.ajax({
        			url: "<?php echo JURI::base(); ?>index.php?option=com_userprofile&task=user.get_ajax_data&getcalculatetype=1&userid=<?php echo $user;?>&munits="+$joomla('select[name="txtMeasurementUnits"]').val() +"&tos="+$joomla('select[name="txtTypeOfShipperName"]').val() +"&stype="+$joomla('select[name="txtServiceType"]').val() +"&source="+$joomla('select[name="txtSourceCntry"]').val() +"&dt="+$joomla('select[name="txtDestinationCntry"]').val() +"&length="+$joomla('input[name="txtLengthIds"]').val() +"&width="+$joomla('input[name="txtWidthIds"]').val() +"&height="+$joomla('input[name="txtHeightIds"]').val() +"&qty="+$joomla('input[name="txtQuantityIds"]').val() +"&gwt="+$joomla('input[name="txtWeightIds"]').val() +"&wtunits="+$joomla('select[name="txtWeightUnits"]').val()+"&getcalculateflag=1&jpath=<?php echo urlencode  (JPATH_SITE); ?>&pseudoParam="+new Date().getTime(),
        			data: { "getpackagetype": $joomla(this).data('id') },
        			dataType:"html",
        			type: "get",
        			async: false,
        			beforeSend: function() {
        			  $joomla(':input[type="submit"]').prop('disabled', true);  
                      $joomla(".page_loader").show();
                      $joomla(":button").attr("disabled", true); 
                   },success: function(data){
                      $joomla(':input[type="submit"]').prop('disabled', false);    
                      $joomla(":button").attr("disabled", false); 
                      console.log(data);
                      $joomla(".page_loader").hide(); 
                      var cospor=data;
                      cospor=cospor.split(":");
                      $joomla('#divApiResult').html('');
                      $joomla('#divquotationCost').html('');
                      if(cospor[3]>0){
                         $joomla('input[name=txtRatetypeIds]').val(cospor[0]);
                         //console.log("Valuem:"+cospor[1]);
                         var gw=cospor[1];
                         gw=gw.split(",");
                         $joomla.each( gw, function( key, value ) {
                            var ift=key+1;
                            var subStr = parseFloat(value);
                            //var subStr = value.substring(0, 4);
                            $joomla('#gtable tr').eq(ift).find('#divVolumeMultiple').html(subStr.toFixed(2));
                         });
                         $joomla('input[name=txtVolumeMultiple]').val(cospor[1]);
                         var vw=cospor[2];
                         vw=vw.split(",");
                         $joomla.each( vw, function( key, value ) {
                            var vift=key+1;
                            var subStrs = parseFloat(value);
                            //var subStrs = value.substring(0, 5);
                            $joomla('#gtable tr').eq(vift).find('#divVolWtMultiple').html(subStrs.toFixed(2));
                         });
                         $joomla('input[name=txtVolWtMultiple]').val(cospor[2]);
                         
                         $joomla('#divquotationCost').html('');
                         $joomla('#divquotationCost').html('$ '+cospor[3]);
                         $joomla('input[name=txtIdServ]').val(cospor[4]);
                         $joomla('input[name=txtIdRate]').val(cospor[5]);
                         $joomla('#divdiscountCost').html('');
                            if(cospor[6]==""){
                            cospor[6]="0.00";
                            }
                            $joomla('#divdiscountCost').html('$'+cospor[6]);
                            $joomla('input[name=txtDiscount]').val(cospor[6]);

                         var quotcost=0;
                         quotcost=cospor[3];
                         $joomla('input[name=txtquotationCost]').val(quotcost);
        
                         var adds=0;
                         adds=cospor[7];
                         adds = adds.split(',');
                         var sum = 0;
                         for(j=0;j<adds.length;j++){
                           console.log(parseFloat(adds[j]));
                           if(isNaN(parseFloat(adds[j])))
                           sum += 0;
                           else
                           sum += parseFloat(adds[j]);
                         }
                         if(isNaN(sum)){
                           sum='0.00';
                         }
                         sum=sum.toFixed(2);
                         $joomla('input[name=txtAdditionalServices]').val(cospor[7]);
                         $joomla('#divadditionalCost').html('');
                         $joomla('#divadditionalCost').html('$'+sum);                
                         
                         var dgw=cospor[8];
                         dgw=dgw.split(",");
                         $joomla.each( dgw, function( key, value ) {
                            var vdgw=key+1;
                            $joomla('#gtable tr').eq(vdgw).find('#divGrossWeight').html(value);
                         });
                         $joomla('input[name=txtGrossWeight]').val(cospor[8]);
                         $joomla('#divfinalCost').html('');
                         var fico=0;
                         fico=parseFloat(quotcost)+parseFloat(sum);
                         if(cospor[6]>0){
                             fico=parseFloat(fico)-parseFloat(cospor[6]);
                         }else{
                             $joomla('input[name=txtDiscount]').val(0);
        
                         }
                         if(fico!="NaN"){
                            $joomla('#divfinalCost').html('$'+fico.toFixed(2));
                            $joomla('input[name=txtfinalCost]').val(fico);
                         }

                         $joomla('input[name=txtAdditionalServicesId]').val(cospor[9]);
                         testing = true;
                      }else{
                        $joomla('#divApiResult').html('<div class="col-sm-12"><label class="error">'+data+'</label></div>');
                        return false;
                      }
                      console.log(data);
                      return true;
                   }
        		});
        		return testing;
        	
            }else{
                return true;
            }
        }
       });    
    });
    
    $joomla(document).on("keyup","input[name*='txtLength'],input[name*='txtWidth'],input[name*='txtHeight'],input[name*='txtWeight']",function(e){
    this.value = this.value.replace(/[^0-9.]/g, '');
    //if (/\D/g.test(this.value))
    });
    
    $joomla(document).on("keyup","input[name*='txtQuantity']",function(e){
    this.value = this.value.replace(/[^0-9]/g, '');
    //if (/\D/g.test(this.value))
    });
    
   
    
});
function validation(){
         var tqty='';
        $joomla('input[name="txtQuantity[]"]').each(function(){
            if($joomla(this).val()>=1)
            tqty += $joomla(this).val()+',';
        });    
        console.log("tqty:"+tqty);
        var twt='';
        $joomla('input[name="txtWeight[]"]').each(function(){
            if($joomla(this).val()>=1)
            twt += $joomla(this).val()+',';
        });
        var twd='';
        $joomla("#gtable tr").each(function(){
          if($joomla(this).find('#txtWidth').val()==""){
          }else{
            if($joomla(this).find('#txtWidth').val()>=1)
            twd += $joomla(this).find('#txtWidth').val()+',';
          }
        });
        var tht='';
        $joomla("#gtable tr").each(function(){
          if($joomla(this).find('#txtHeight').val()==""){
          }else{
            if($joomla(this).find('#txtHeight').val()>=1)
            tht += $joomla(this).find('#txtHeight').val()+',';
          }
        });
        var tlt='';
        $joomla("#gtable tr").each(function(){
          if($joomla(this).find('#txtLength').val()==""){
          }else{
            if($joomla(this).find('#txtLength').val()>=1)
            tlt += $joomla(this).find('#txtLength').val()+',';
          }
        });
        $joomla('input[name="txtQuantityIds"]').val(tqty);    
        $joomla('input[name="txtWeightIds"]').val(twt);    
        $joomla('input[name="txtWidthIds"]').val(twd);    
        $joomla('input[name="txtHeightIds"]').val(tht);    
        $joomla('input[name="txtLengthIds"]').val(tlt); 
        
        return true;   
}
</script>
<div class="container">
	<div class="main_panel persnl_panel">
		<div class="main_heading"><?php echo $assArr['qUOTATION'];?></div>
		<div class="panel-body">
			<div class="row">
				<div class="col-sm-12">
					<h4 class="sub_title"><strong><?php echo $assArr['Online_Quote_Id'];?> : <?php echo $resWp->Quote_Id;?></strong></h4>
					
				</div>
			<div class="col-sm-12 text-right">
					<p><?php echo $assArr['Field_with_asterisks_are_required']; ?></p>	
			</div>
			
			</div>

        <form name="userprofileFormOne" id="userprofileFormOne" method="post" action="" enctype="multipart/form-data">

            <div class="row">
                 <?php  if(strtolower($elem['Source'][1]) == strtolower("ACT")){ ?>
			    <div class="col-sm-12 col-md-6">
			        <div class="form-group">
			            <label><?php echo $assArr['source_Country'];  ?> <span class="error">*</span></label>
			            <select class="form-control" name="txtSourceCntry" id="txtSourceCntry">
			                <option value="">Select</option>
			                <?php
			                    foreach($resWp->SourceCntry_List as $key=>$row){
			                        echo '<option value="'.$row->id_values.'">'.$row->desc_vals.'</option>';
			                    }
			                ?>
			            </select>
			        </div>
			    </div>
			    <?php } if(strtolower($elem['Destination'][1]) == strtolower("ACT")){ ?>
			    <div class="col-sm-12 col-md-6">
			        <div class="form-group">
			        	<!--<label class="invisable d-block">&nbsp;</label>-->
			            <label><?php echo $assArr['destination_country']; ?>:&nbsp;<span class="error">*</span></label>
			            <select class="form-control" name="txtDestinationCntry" id="txtDestinationCntry">
			                <option value="">Select</option>
			                <?php
			                    foreach($resWp->DestinationCntry_List as $key=>$row){
			                        echo '<option value="'.$row->id_values.'">'.$row->desc_vals.'</option>';
			                    }
			                ?>
			            </select>
			        </div>
			    </div>
			    <?php } ?>
			</div>

			<div class="row">
			    <?php if(strtolower($elem['TypeOfShipment'][1]) == strtolower("ACT")){ ?>
			    <div class="col-sm-3 col-md-3">
			        <div class="form-group">
			            <label><?php echo $assArr['type_of_shipment'];  ?> <span class="error">*</span></label>
			            <select class="form-control" name="txtTypeOfShipperName">
			                <option value="">Select</option>
			                <?php
			                    foreach($resWp->Shipment_List as $key=>$row){ ?>
			                        
			                 <option data-id="<?php echo $row->value; ?>" value="<?php echo $row->id_values; ?>" <?php if (strtolower($row->id_values) == strtolower($elem['TypeOfShipment'][4]) ) { echo "selected"; } ?>  ><?php echo $row->desc_vals;  ?></option>
			                        
			                        <!--echo '<option value="'.strtoupper($row->id_values).'">'.$row->desc_vals.'</option>';-->
			                  <?php } ?>
			                
			            </select>
			        </div>
			    </div>
			    <?php } if(strtolower($elem['ServiceType'][1]) == strtolower("ACT")){ ?>
			    <div class="col-sm-3 col-md-3">
			        <div class="form-group">
			            <label><?php echo $assArr['service_Type'];?> <span class="error">*</span></label>
		                <select class="form-control" name="txtServiceType">
			                <option value="">Select</option>
			            </select>
			        </div>
			    </div>	
			     <?php } if(strtolower($elem['WeightUnits'][1]) == strtolower("ACT")){ ?>
			    <div class="col-sm-3 col-md-3">
			        <div class="form-group">
			        	<!--<label class="invisable d-block">&nbsp;</label>-->
			            <label><?php echo $assArr['weight_Units']; ?>:&nbsp;<span class="error">*</span></label>
			            <select class="form-control" name="txtWeightUnits">
			                <option value="">Select</option>
			                <?php
			                    foreach($resWp->WeightUnits_List as $key=>$row){?>
			                     <option data-id="<?php echo $row->value; ?>" value="<?php echo $row->id_values; ?>" <?php if (strtolower($row->id_values) == strtolower($elem['WeightUnits'][4]) ) { echo "selected"; } ?>  ><?php echo $row->desc_vals;  ?></option>
			                      <!--echo '<option value="'.$row->id_values.'">'.$row->desc_vals.'</option>';-->
			                   <?php } ?>
			                
			            </select>
			        </div>
			    </div>
			     <?php } if(strtolower($elem['MeasurementUnits'][1]) == strtolower("ACT")){ ?>
			    
			    <div class="col-sm-3 col-md-3">
			        <div class="form-group">
			            <label><?php echo $assArr['measurement_Units'];  ?> <span class="error">*</span></label>
			            <select class="form-control"  name="txtMeasurementUnits" >
			                <option value="">Select</option>
			                <?php
			                    foreach($resWp->MeasurementUnits_List as $key=>$row){ ?>
			                    <option data-id="<?php echo $row->value; ?>" value="<?php echo $row->id_values; ?>" <?php if (strtolower($row->id_values) == strtolower($elem['MeasurementUnits'][4]) ) { echo "selected"; } ?>  ><?php echo $row->desc_vals;  ?></option>
			                        <!--echo '<option value="'.$row->id_values.'">'.$row->desc_vals.'</option>';-->
			                   <?php } ?>
			                
			            </select>
			        </div>
			    </div>
			    <?php } ?>
			</div>
			
             <div class="row">
                 
			<div class="col-sm-12 col-md-6">
            <div class="rdo_cust">
                <div class="rdo_rd1">
                  <input type="checkbox" name="txtIns" value='true' class="chknew">
                  <label><?php echo $assArr['If a special equipment required_fill in the Notes'];?>?</label>
                </div>
            </div>
             <?php  if(strtolower($elem['Notes'][1]) == strtolower("ACT")){ ?>
            <div class="form-group">
                <label><?php echo $assArr['notes']; ?><span class="error msgs">*</span></label>
               <input type="text" class="form-control" name="txtNotes" maxlength="250" value="<?php echo $elem['Notes'][4]; ?>" <?php if($elem['Notes'][3]){ ?> readonly <?php } ?>>  
            </div>
            <?php } ?>
            
            <?php 
            
            if($menuCustType == "COMP"){  
                
                $businessTypes = Controlbox::GetBusinessTypes($user);
                
            ?>
            
            
            
            <div class="form-group">
                <label><?php echo Jtext::_('Business Type')  ?> </label>
               <select  class="form-control" name="txtBusinessType" id="txtBusinessType"> 
               <option value="">Select Business Type</option>
               
            <?php 
            foreach($businessTypes as $type){
                if($type->is_shown == "true"){
                        echo '<option value="'.$type->desc_vals.'">'.$type->desc_vals.'</option>';
               
                } 
            } 
            ?>
               
               </select>
            </div>
            
            <?php } ?>
			  
          </div>      	
			</div>
            <div class="clearfix"></div>
            <div class="row">
	        	<div class="col-md-12">
                <div class="table-responsive">
	        		<table class="table table-bordered theme_table" id="gtable">
	        			<thead>
							<tr>
								<th><?php echo $assArr['item_name'];  ?></th>
								<th><?php echo  $assArr['package']; ?></th>
								<!--<th><?php echo Jtext::_('COM_USERPROFILE_QUOTATION_TABLE_COMMODITY')  ?></th>-->
								<th><?php echo $assArr['quantity']; ?> </th>
								<th><?php echo  $assArr['length']; ?> </th>
								<th><?php echo $assArr['width']; ?> </th>
								<th><?php echo $assArr['height'];  ?></th>
								<th><?php echo $assArr['gROSS_WT/ITEM']; ?></th>
								<th><?php echo $assArr['gROSS_WT'];  ?></th>
								<th><?php echo  $assArr['vOLUME'];?></th>
								<th><?php echo $assArr['vOLUMETRIC_WT'];  ?></th>
							</tr>
	        			</thead>
	        			<tbody><tr><td><input type="text" class="form-control" name="txtItemName[]" maxlength="30" id="4"><div id="errsItemname"></div>  </td>
	        			<td><select class="form-control" name="txtPackageList[]" id="1">
			                <option value="">Select</option>
			                <?php
			                    foreach($resWp->Package_List as $key=>$row){
			                        echo '<option value="'.$row->id_values.':'.$row->desc_vals.'">'.$row->desc_vals.'</option>';
			                    }
			                ?>
			            </select>
			            <!--</td><td><select class="form-control" id="4" name="txtCommodity[]">-->
			            <!--    <option value="">Select</option>-->
			                <?php
			                
			                 //   foreach($resWp->Commodity_List as $key=>$row){
			                 //       echo '<option value="'.$row->id_values.'">'.$row->desc_vals.'</option>';
			                 //   }
			                ?>
			            </select></td>
			            <td><input type="text" class="form-control" name="txtQuantity[]" maxlength="3" id="2"><div id="errsQuantity"></div> </td>
			            <td><input type="text" class="form-control" name="txtLength[]" id="txtLength" maxlength="6" >  </td>
			            <td><input type="text" class="form-control" name="txtWidth[]" id="txtWidth" maxlength="6" >  </td>
			            <td><input type="text" class="form-control" name="txtHeight[]" id="txtHeight" maxlength="6" >  </td>
			            <td><input type="text" class="form-control" name="txtWeight[]" id="3" maxlength="10"><div id="errsWeight"></div></td>
			            <td><div id="divGrossWeight"></div></td>
			            <td><div id="divVolumeMultiple"></div></td>
			            <td><div id="divVolWtMultiple"></div>  </td>
			            <!--<td><input type="button" name="addrow" value="+" class="title btn btn-primary btn-add"> <input class="title btn btn-danger btn-rem" type="button" name="deleterow" value="x"></td>-->
			            </tr></tbody>
	        		</table>
	        	</div>
                </div>
	        </div>
	        
	         <div id="divApiResult"></div>
	        
			<div class="shp-blk1 quot-shiprates">
				<table width="100%" class="table">
					<tr>
						<td><p class="shpprice"> <label><?php echo $assArr['shipping_Cost'];  ?> </label><span id="divquotationCost" >$0.00</span> </p></td>
						<td><p class="shpprice"><label><?php echo $assArr['additional_Cost'];  ?> </label><span id="divadditionalCost" >$0.00</span> </p></td>
						<td><p class="shpprice"><label><?php echo $assArr['discount']; ?> </label><span id="divdiscountCost" >$0.00</span></p></td>
						<td><p class="shpprice"><label><?php echo $assArr['final_Cost']; ?> </label><span id="divfinalCost" >$0.00</span></p></td>
					</tr>
				</table>
                    <div id="loading-image" style="display:none" ><img src="/components/com_userprofile/images/loader.gif"></div>
                    <div class="clearfix"></div>
	        </div>
			<div class="row">
			    <div class="col-sm-12 col-md-12 text-center">
			
		    <input type="hidden" class="form-control" name="txtAdditionalServices">
		    <input type="hidden" class="form-control" name="txtAdditionalServicesId">
		    <input type="hidden" class="form-control" name="txtGrossWeight">
			
		    <input type="hidden" class="form-control" name="txtQuantityIds">
		    <input type="hidden" class="form-control" name="txtLengthIds">
		    <input type="hidden" class="form-control" name="txtWidthIds">
		    <input type="hidden" class="form-control" name="txtHeightIds">
		    <input type="hidden" class="form-control" name="txtWeightIds">
		    <input type="hidden" class="form-control" name="txtRatetypeIds">

            <input type="hidden" class="form-control" name="txtVolumeMultiple">
            <input type="hidden" class="form-control" name="txtVolWtMultiple">
            <input type="hidden" class="form-control" name="txtquotationCost" value=0>

            <input type="hidden" class="form-control" name="txtIdServ">
			<input type="hidden" class="form-control" name="txtIdRate">
			<input type="hidden" class="form-control" name="txtDiscount">
			<input type="hidden" class="form-control" name="txtfinalCost">
              
			        
			        
			        <input type="button" name="btnCalculate" id="btnCalculate" value="<?php echo $assArr['calculate'];  ?>" class="btn btn-primary">
			        <input type="submit" name="submit" disabled="true" value="<?php echo $assArr['submit'];  ?>" class="btn btn-primary">
			    </div>
			</div>
			
		
			
          <input type="hidden" name="task" value="user.insertQotation">
          <input type="hidden" name="id" value="0" />
          <input type="hidden" name="quotid" value="<?php echo $resWp->Quote_Id;?>" />
          <input type="hidden" name="user" value="<?php echo $user;?>" />
			</form>
		</div>
	</div>
</div>


<!-- Modal -->
  <div id="exampleModal" class="modal fade" role="dialog">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <div class="text-right">
          <input type="button" value="Close" data-dismiss="modal" class="btn btn-danger">
          </div>
          <h4 class="modal-title"><strong><?echo $assArr['view_Shipments'];?></strong></h4>
        </div>
        <div class="modal-body">
          <div class="row">
            <div class="col-sm-12 col-md-6">
              <div class="form-group">
                <label>Back Company / Dealer <span class="error">*</span></label>
                <input type="text" name="txtBackCompany" class="form-control">
              </div>
            </div>
        
        
      </div>
    </div>
  </div>
  <script>
    $joomla('document').ready(function(){
            // dropdowns
    
     mesDrop = "<?php echo $elem['MeasurementUnits'][3];  ?>";
    if(mesDrop){
        $joomla('select[name="txtMeasurementUnits"]').attr("style", "pointer-events: none;");
        
    }else{
        $joomla('select[name="txtMeasurementUnits"]').attr("style", "pointer-events: auto;");
    }
    
    //measurment units
     weigDrop = "<?php echo $elem['WeightUnits'][3];  ?>";
    
      if(weigDrop){
        $joomla('select[name="txtWeightUnits"]').attr("style", "pointer-events: none;");
        
    }else{
        $joomla('select[name="txtWeightUnits"]').attr("style", "pointer-events: auto;");
    }
    //shipping  type
    
     shipDrop = "<?php echo $elem['TypeOfShipment'][3];  ?>";
    
      if(shipDrop){
        $joomla('select[name="txtTypeOfShipperName"]').attr("style", "pointer-events: none;");
        
    }else{
        $joomla('select[name="txtTypeOfShipperName"]').attr("style", "pointer-events: auto;");
    }
    
    //service type
    
      ratDrop = "<?php echo $elem['ServiceType'][3];  ?>";
    
    if(ratDrop){
        $joomla('select[name="txtServiceType"]').attr("style", "pointer-events: none;");
        
    }else{
        $joomla('select[name="txtServiceType"]').attr("style", "pointer-events: auto;");
    }
    
    
    // destination country
    
    destCounDrop = "<?php echo $elem['Destination'][3];  ?>";
  
    
     if(destCounDrop){
        $joomla('select[name="txtDestinationCntry"]').attr("style", "pointer-events: none;");
        
    }else{
        $joomla('select[name="txtDestinationCntry"]').attr("style", "pointer-events: auto;");
    }
    
    destCounDefDrop = "<?php echo $elem['Destination'][4];  ?>";
   
    if(destCounDefDrop !=""){
        $joomla('select[name="txtDestinationCntry"]').val(destCounDefDrop).trigger("change");
    }
    
    
    // source country
    
    sourceCounDrop = "<?php echo $elem['Source'][3];  ?>";
  
    
     if(sourceCounDrop){
        $joomla('select[name="txtSourceCntry"]').attr("style", "pointer-events: none;");
        
    }else{
        $joomla('select[name="txtSourceCntry"]').attr("style", "pointer-events: auto;");
    }
    
    sourceCounDefDrop = "<?php echo $elem['Source'][4];  ?>";
   
    if(sourceCounDefDrop !=""){
        $joomla('select#txtSourceCntry').val(sourceCounDefDrop).trigger("change");
    }
    
    });
    
    $joomla("input[type='txtIns']").change(function(){
        
      if($joomla("input[type='txtNotes']").prop("checked")) {
          
    $joomla(".error msgs").show();
    
  } else {
    $joomla(".error msgs").hide();
  }
});

$joomla(".error.msgs").hide();
$joomla("input[name='txtIns']").click(function() {
    // alert("fgbjfgjk");
    if($joomla(this).is(":checked")) {
        $joomla(".error.msgs").show();
        // $joomla("input[name='txtNotes']").html("Please select a Project ID");
        // alert("this field is required");
    } else {
        $joomla(".error.msgs").hide();
    }
});
</script>


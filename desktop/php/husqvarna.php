<?php
if (!isConnect('admin')) {
    throw new Exception('{{401 - Accès non autorisé}}');
}
sendVarToJS('eqType', 'husqvarna');
?>

<div class="row row-overflow">
    <div class="col-lg-3">
        <div class="bs-sidebar">
            <ul id="ul_eqLogic" class="nav nav-list bs-sidenav">
                <a class="btn btn-default eqLogicDetect" style="width : 100%;margin-top : 5px;margin-bottom: 5px;"><i class="fa fa-refresh"></i> {{Detecter}}</a>
                <?php
				$eqLogics = eqLogic::byType('husqvarna');
				foreach ($eqLogics as $eqLogic) {
                    echo '<li>'."\n";
						echo '<a class="cursor li_eqLogic" data-eqLogic_id="' . $eqLogic->getId() . '" data-eqLogic_type="husqvarna"><i class="fa fa-download"></i> ' . $eqLogic->getName() . '</a>'."\n";
					echo '</li>'."\n";
                }
                ?>
            </ul>
        </div>
    </div>
    <div class="col-lg-9 col-md-9 col-sm-8 eqLogicThumbnailDisplay" style="border-left: solid 1px #EEE; padding-left: 25px;">
  <legend><i class="fa fa-cog"></i>  {{Gestion}}</legend>
   <div class="eqLogicThumbnailContainer">
    <div class="cursor eqLogicDetect" style="background-color : #ffffff; height : 120px;margin-bottom : 10px;padding : 5px;border-radius: 2px;width : 160px;margin-left : 10px;" >
     <center>
      <i class="fa fa-refresh" style="font-size : 6em;color:#94ca02;"></i>
    </center>
    <span style="font-size : 1.1em;position:relative; top : 15px;word-break: break-all;white-space: pre-wrap;word-wrap: break-word;color:#94ca02"><center>{{Detecter}}</center></span>
  </div>
  <div class="cursor eqLogicAction" data-action="gotoPluginConf" style="background-color : #ffffff; height : 120px;margin-bottom : 10px;padding : 5px;border-radius: 2px;width : 160px;margin-left : 10px;">
    <center>
      <i class="fa fa-wrench" style="font-size : 6em;color:#767676;"></i>
    </center>
    <span style="font-size : 1.1em;position:relative; top : 15px;word-break: break-all;white-space: pre-wrap;word-wrap: break-word;color:#767676"><center>{{Configuration}}</center></span>
  </div>
</div>
        <legend>{{Mes equipements}}
        </legend>
		<div class="eqLogicThumbnailContainer">
			<?php
	        if (count($eqLogics) == 0) {
				echo "<br/><br/><br/><center><span style='color:#767676;font-size:1.2em;font-weight: bold;'>{{Vous n'avez pas encore de husqvarna, cliquez sur Detecter un équipement pour commencer}}</span></center>";
			} else {
                foreach ($eqLogics as $eqLogic) {
                    echo '<div class="eqLogicDisplayCard cursor" data-eqLogic_id="' . $eqLogic->getId() . '" style="background-color : #ffffff; height : 200px;margin-bottom : 10px;padding : 5px;border-radius: 2px;width : 160px;margin-left : 10px;" >';
                    echo "<center>";
                    echo '<img src="plugins/husqvarna/plugin_info/husqvarna_icon.png" height="105" width="95" />';
                    echo "</center>";
                    echo ' <span style="font-size : 1.1em;position:relative; top : 15px;word-break: break-all;white-space: pre-wrap;word-wrap: break-word;"> <center>' . $eqLogic->getHumanName(true, true) . '</center></span>';
                    echo '</div>';
                }
                ?>
            </div>
        <?php } ?>
    </div>
    <div class="col-lg-9 eqLogic husqvarna" style="border-left: solid 1px #EEE; padding-left: 25px;display: none;">
        <form class="form-horizontal">
            <fieldset>
                <legend>
                   <i class="fa fa-arrow-circle-left eqLogicAction cursor" data-action="returnToThumbnailDisplay"></i> {{Général}}
				   <i class='fa fa-cogs eqLogicAction pull-right cursor expertModeVisible' data-action='configure'></i>
			   </legend>
                <div class="form-group">
                    <label class="col-lg-2 control-label">{{Identifiant de l'équipement}}</label>
                    <div class="col-lg-3">
                        <input type="text" class="eqLogicAttr form-control" data-l1key="logicalId" readonly/>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-lg-2 control-label">{{Nom de l'équipement}}</label>
                    <div class="col-lg-3">
                        <input type="text" class="eqLogicAttr form-control" data-l1key="id" style="display : none;" />
                        <input type="text" class="eqLogicAttr form-control" data-l1key="name" placeholder="{{Nom de la husqvarna}}"/>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-lg-2 control-label" >{{Objet parent}}</label>
                    <div class="col-lg-3">
                        <select class="form-control eqLogicAttr" data-l1key="object_id">
                            <option value="">{{Aucun}}</option>
                            <?php
                            foreach (object::all() as $object) {
                                echo '<option value="' . $object->getId() . '">' . $object->getName() . '</option>'."\n";
                            }
                            ?>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-lg-2 control-label">{{Catégorie}}</label>
                    <div class="col-lg-8">
                        <?php
                        foreach (jeedom::getConfiguration('eqLogic:category') as $key => $value) {
                            echo '<label class="checkbox-inline">'."\n";
                            echo '<input type="checkbox" class="eqLogicAttr" data-l1key="category" data-l2key="' . $key . '" />' . $value['name'];
                            echo '</label>'."\n";
                        }
                        ?>

                    </div>
                </div>
                <div class="form-group">
                    <label class="col-md-2 control-label" >{{Activer}}</label>
                    <div class="col-md-1">
                        <input type="checkbox" class="eqLogicAttr" data-l1key="isEnable" checked/>
                    </div>
                    <label class="col-lg-2 control-label" >{{Visible}}</label>
                    <div class="col-lg-1">
                        <input type="checkbox" class="eqLogicAttr" data-l1key="isVisible" checked/>
                    </div>
                </div>
            </fieldset> 
        </form>

        <legend>{{Indicateurs}}</legend>
        <table id="table_cmd" class="table table-bordered table-condensed">
            <thead>
                <tr>
                    <th style="width: 50px;">#</th>
                    <th style="width: 230px;">{{Nom}}</th>
                    <th style="width: 110px;">{{Sous-Type}}</th>
                    <th style="width: 100px;">{{Paramètres}}</th>
                    <th style="width: 200px;"></th>
                </tr>
            </thead>
            <tbody>

            </tbody>
        </table>

        <form class="form-horizontal">
            <fieldset>
                <div class="form-actions">
                    <a class="btn btn-danger eqLogicAction" data-action="remove"><i class="fa fa-minus-circle"></i> {{Supprimer}}</a>
                    <a class="btn btn-success eqLogicAction" data-action="save"><i class="fa fa-check-circle"></i> {{Sauvegarder}}</a>
                </div>
            </fieldset>
        </form>

    </div>
</div>

<?php include_file('desktop', 'husqvarna', 'js', 'husqvarna'); ?>
<?php include_file('core', 'plugin.template', 'js'); ?>

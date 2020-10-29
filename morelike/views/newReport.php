<!--Se agrega la vista para mostrar los reportes-->

<div class="row">
	<div class="col">
		<h3 class="text-center">Reportes</h3>
		<hr>
		<h4 class="text-center">Accesos de usuarios</h4>  
        
        <!---tabla para mostrar --->
        <table class="table table-striped">
			<th>Nombre</th>
			<th>Último acceso</th>
			<!--se añaden las etiquetas de php que faltan -->
			<?php foreach($usuarios as $row):?>
				<tr>
					
                       
                        <td><?=$row->nombre?></td>
				
					
                    <td><?=$row->acceso?></td>
					
				</tr>
			<?php endforeach;?>
		</table>

	</div>
</div>
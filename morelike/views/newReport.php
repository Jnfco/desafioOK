<!--Se agrega la vista para mostrar los reportes-->

<div class="row">
	<div class="col">
		<h3 class="text-center">Reportes</h3>
		<hr>
		<h4 class="text-center">Accesos de usuarios</h4>

        <!--tabla para mostrar -->
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
        <h3 class="text-center">Registros por día</h3>
        <table class="table table-striped">
			<th>Fecha</th>
            <th>Descripción</th>
            <th>Ingreso</th>
            <th>Egreso</th>
			<!--se añaden las etiquetas de php que faltan -->
			<?php foreach($data as $row):?>
				<tr>
                    <td><?=$row->fecha?></td>
                    <td><?=$row->descripcion?></td>
                    <td><?=number_format($row->ingreso,0,",",".")?></td>
                    <td><?=number_format($row->egreso,0,",",".")?></td>
				</tr>
			<?php endforeach;?>
		</table>

		<h3 class="text-center">Ingreso y egreso total por dia</h3>
		<table class="table table-striped">
			<th>Fecha</th>
			<th>Ingreso total</th>
			<th>Egreso total</th>
			<?php foreach($total as $row):?>
				<tr>
					<td><?=$row->fecha?></td>
					<td><?=number_format($row->ingreso,0,",",".")?></td>
					<td><?=number_format($row->egreso,0,",",".")?></td>
				</tr>
			<?php endforeach;?>
		</table>
	</div>
</div>
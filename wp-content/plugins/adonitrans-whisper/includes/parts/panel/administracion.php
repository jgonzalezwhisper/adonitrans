<?php require_once($_SERVER['DOCUMENT_ROOT'] . '/wp-load.php');  ?>
<div class="tarjeta">
	<div class="wrap-titulo">
		<h3 class="titulo">ADMINISTRACIÓN</h3>
		<h4 class="subtitulo">Gestiona la configuración del software de la empresa</h4>
	</div>
	<p>Administra y gestiona fácilmente las configuraciones necesarias para el funcionamiento correcto del software.</p>
</div>

<div class="adonitrans-tabs tarjeta">
	<ul class="adonitrans-tabs-nav">
		<li class="active" data-tab="tab1">Franjas Horarias</li>
		<li data-tab="tab2">Descuentos</li>
	</ul>
	<form id="ajustes-generales">
		<div class="adonitrans-tabs-content">
			<!-- Tab 1 -->
			<div id="tab1" class="tab-content active">
				<p>Gestiona las franjas horarias para los conductores de la empresa.</p>
				<!-- Repetidor para franjas horarias -->
				<div id="wrap-franjas-trabajo">
					<div id="wrap-franjas">
						<?php
						$franjas_horas_trabajo = get_field('franjas_horas_trabajo', 'option');
						if ($franjas_horas_trabajo) :
						foreach ($franjas_horas_trabajo as $franja) :
							$hora_inicio = date('H:i', strtotime($franja['hora_inicio']));
							$hora_fin = date('H:i', strtotime($franja['hora_fin']));
						?>
						<div class="franja">
							<div>
								<label for="franja_nombre_<?php echo $franja['nombre']; ?>">Nombre</label>
								<input type="text" id="franja_nombre_<?php echo $franja['nombre']; ?>" name="nombre_franja[]" value="<?php echo esc_attr($franja['nombre']); ?>"  />
							</div>
							<div>
								<label for="franja_inicio_<?php echo $hora_inicio; ?>">Hora Inicio</label>
								<input type="time" id="franja_inicio_<?php echo $hora_inicio; ?>" name="inicio_franja[]" value="<?php echo esc_attr($hora_inicio); ?>"  />
							</div>
							<div>
								<label for="franja_fin_<?php echo $hora_fin; ?>">Hora Fin</label>
								<input type="time" id="franja_fin_<?php echo $hora_fin; ?>" name="fin_franja[]" value="<?php echo esc_attr($hora_fin); ?>"  />
							</div>
							<button type="button" class="button remove-franja-row">Eliminar Franja</button>
						</div>
						<?php
						endforeach;
						else :
						?>
						<div class="franja">
							<div>
								<label for="franja_nombre_<?php echo $franja['nombre']; ?>">Nombre</label>
								<input type="text" id="franja_nombre_<?php echo $franja['nombre']; ?>" name="nombre_franja[]" value="<?php echo esc_attr($franja['nombre']); ?>"  />
							</div>
							<div>
								<label for="franja_inicio_<?php echo $hora_inicio; ?>">Hora Inicio</label>
								<input type="time" id="franja_inicio_<?php echo $hora_inicio; ?>" name="inicio_franja[]" value="<?php echo esc_attr($hora_inicio); ?>"  />
							</div>
							<div>
								<label for="franja_fin_<?php echo $hora_fin; ?>">Hora Fin</label>
								<input type="time" id="franja_fin_<?php echo $hora_fin; ?>" name="fin_franja[]" value="<?php echo esc_attr($hora_fin); ?>"  />
							</div>
							<button type="button" class="button remove-franja-row">Eliminar Franja</button>
						</div>
						<?php
						endif;
						?>
					</div>
					<button type="button" id="add-franja-row" class="button">Añadir Franja</button>
				</div>
			</div>

			<!-- Tab 2 -->
			<div id="tab2" class="tab-content">
				<p>Gestiona las tarifas por defecto a aplicar a los conductores.</p>
				<div id="wrap-tarifas-descuentos">
					<div id="wrap-tarifas">
						<?php
							$tarifas_descuentos = get_field('tarifas_descuentos', 'option');
							$tarifas_descuentos = $tarifas_descuentos;
						?>
						<?php foreach ($tarifas_descuentos as $valtardesc):  ?>
						<div class="row-tarifa">
							<div>
								<label for="elm-tarifa-<?php echo $valtardesc['grupo_tarifas_descuentos']['descripcion']; ?>">Descripcion
									<input type="text" id="elm-tarifa-<?php echo $valtardesc['grupo_tarifas_descuentos']['descripcion']; ?>" name="descripcion[]" value="<?php echo esc_attr($valtardesc['grupo_tarifas_descuentos']['descripcion']); ?>"  />
								</label>
							</div>
							<div>
								<label for="elm-tarifa-<?php echo $valtardesc['grupo_tarifas_descuentos']['valor']; ?>">Valor
									<input type="text" id="elm-tarifa-<?php echo $valtardesc['grupo_tarifas_descuentos']['valor']; ?>" name="valor[]" value="<?php echo esc_attr($valtardesc['grupo_tarifas_descuentos']['valor']) ?>"  />
								</label>
							</div>
							<button type="button" class="button remove-tarifa-row">Eliminar Tarifa</button>
						</div>
						<?php endforeach ?>
					</div>
					<button type="button" id="add-tarifa-row" class="button">Añadir Tarifa</button>
				</div>
			</div>
		</div>
		<button type="submit" class="button">Guardar Cambios</button>
	</form>
</div>
<?php
function kame_erp_cron_instructions() {
    ?>
    <div class="wrap">
        <h1>Instrucciones para Configurar el Cron Job</h1>
        <p>Sigue estos pasos para configurar la sincronización automática cada 10 minutos usando un cron job en cPanel:</p>
        <ol>
            <li>Accede a tu cuenta de cPanel.</li>
            <li>Busca la sección "Cron Jobs" y haz clic en ella.</li>
            <li>Añade un nuevo cron job con la siguiente configuración:</li>
            <ul>
                <li><strong>Comando:</strong> <code>php /path/to/your/wp-content/plugins/WP-Kame-Connect-3.2.7/sync.php</code></li>
                <li><strong>Configuración de tiempo:</strong> <code>*/10 * * * *</code> (esto ejecutará el script cada 10 minutos)</li>
            </ul>
        </ol>
    </div>
    <?php
}

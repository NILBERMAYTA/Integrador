<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // coherencia por seguimiento
        DB::unprepared(<<<'SQL'
        CREATE OR REPLACE FUNCTION fn_chk_operacion_detalles() RETURNS trigger AS $$
        DECLARE seg seguimiento_enum;
        BEGIN
            SELECT seguimiento INTO seg FROM articulos WHERE id = NEW.articulo_id;
            IF seg = 'cantidad' THEN
                IF COALESCE(NEW.cantidad,0) <= 0 THEN
                    RAISE EXCEPTION 'Para seguimiento=cantidad, cantidad > 0';
                END IF;
            ELSE
                IF NEW.cantidad IS NOT NULL THEN
                    RAISE EXCEPTION 'Para seguimiento=serie, cantidad se calcula con series';
                END IF;
            END IF;
            RETURN NEW;
        END; $$ LANGUAGE plpgsql;

        DROP TRIGGER IF EXISTS trg_chk_operacion_detalles ON operacion_detalles;
        CREATE TRIGGER trg_chk_operacion_detalles
        BEFORE INSERT OR UPDATE ON operacion_detalles
        FOR EACH ROW EXECUTE FUNCTION fn_chk_operacion_detalles();
        SQL);

        // valida series y sincroniza cantidad = nº de series
        DB::unprepared(<<<'SQL'
        CREATE OR REPLACE FUNCTION fn_guardrails_series() RETURNS trigger AS $$
        DECLARE art_det BIGINT; art_ser BIGINT; seg seguimiento_enum; n INT;
        BEGIN
            SELECT articulo_id INTO art_det FROM operacion_detalles WHERE id = COALESCE(NEW.operacion_detalle_id, OLD.operacion_detalle_id);
            SELECT articulo_id INTO art_ser FROM articulo_series WHERE id = COALESCE(NEW.serie_id, OLD.serie_id);

            IF art_det IS NULL OR art_ser IS NULL OR art_det <> art_ser THEN
                RAISE EXCEPTION 'La serie no corresponde al artículo del detalle';
            END IF;

            SELECT seguimiento INTO seg FROM articulos WHERE id = art_det;
            IF seg <> 'serie' THEN
                RAISE EXCEPTION 'No se pueden asociar series cuando seguimiento=cantidad';
            END IF;

            SELECT COUNT(*) INTO n FROM operacion_detalle_series WHERE operacion_detalle_id = COALESCE(NEW.operacion_detalle_id, OLD.operacion_detalle_id);
            UPDATE operacion_detalles SET cantidad = n WHERE id = COALESCE(NEW.operacion_detalle_id, OLD.operacion_detalle_id);

            RETURN COALESCE(NEW, OLD);
        END; $$ LANGUAGE plpgsql;

        DROP TRIGGER IF EXISTS trg_series_aiud ON operacion_detalle_series;
        CREATE TRIGGER trg_series_aiud
        AFTER INSERT OR UPDATE OR DELETE ON operacion_detalle_series
        FOR EACH ROW EXECUTE FUNCTION fn_guardrails_series();
        SQL);
    }

    public function down(): void
    {
        DB::unprepared("DROP TRIGGER IF EXISTS trg_series_aiud ON operacion_detalle_series");
        DB::unprepared("DROP FUNCTION IF EXISTS fn_guardrails_series()");
        DB::unprepared("DROP TRIGGER IF EXISTS trg_chk_operacion_detalles ON operacion_detalles");
        DB::unprepared("DROP FUNCTION IF EXISTS fn_chk_operacion_detalles()");
    }
};

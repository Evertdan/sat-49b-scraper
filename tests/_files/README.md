# Fixtures reales del DOF — Art. 49 Bis

Datos públicos publicados en el Diario Oficial de la Federación (dof.gob.mx).
**Repo canónico de estos fixtures: `sat-49b-scraper` (este directorio).** Copia espejo
byte-idéntica en `listasnegraslw-/ln69-backend/src/test/resources/dof/` — los sha256 de
abajo son la referencia para re-verificar la paridad (pendiente automatizarla: DW-6).

**PROHIBIDO modificar estos archivos.** Son evidencia byte a byte del DOF real; cualquier
re-indentado o re-codificación invalida la paridad PHP↔Java. Ambos repos llevan
`.gitattributes` con `-text` para que git no normalice sus EOL mixtos (CRLF/LF).

## Captura (2026-07-23)

Comando exacto (bytes crudos, sin re-codificar; TLS verificado SIN `--insecure`):

```
curl -sS --max-time 90 --cacert dof-chain.pem --output <archivo> "<URL>"
```

donde `dof-chain.pem` = intermedio GoDaddy G2 (`https://certs.godaddy.com/repository/gdig2.crt.pem`,
sha256 `97:3A:41:27:6F:FD:01:E0:27:A2:AA:D4:9E:34:C3:78:46:D3:E9:76:FF:6A:62:0B:67:12:E3:38:32:04:1A:A6`)
+ raíz G2 (`gdroot-g2.crt`). Necesario porque `www.dof.gob.mx` envía la cadena INCOMPLETA
(leaf sin intermedio; `openssl` error 21) — evidencia para el spike TLS (Story 1.3) y el
adaptador Java (Story 1.6).

Advertencia de re-captura: las páginas embeben contenido del día de la consulta
("Tipo de Cambio y Tasas al 23/07/2026": dólar, UDIS, TIIE, Cetes), así que una
re-descarga de las mismas URLs JAMÁS será byte-idéntica a estos archivos aunque el
contenido 49 Bis no cambie. La captura se hizo 13 días después de la publicación; los
bytes son los que el DOF servía el 2026-07-23.

## Inventario

| Fixture | sha256 | Fecha pub. | Ed. | URL de origen | Esperado (oráculo) |
|---|---|---|---|---|---|
| `index_20260710_MAT.html` | `bdba2df219ddf8702187b5f5a7ecae4e9fccac19352f3de0a95d5177c5b2f7e1` | 2026-07-10 | MAT | `https://www.dof.gob.mx/index.php?year=2026&month=07&day=10&edicion=MAT` | códigos `[5793257, 5793258, 5793259]` |
| `index_20260710_VES.html` | `7f309f1a0a66eedfaf867c55b089bb247019b6c4f793cdbd0b11f9ea64ce27aa` | 2026-07-10 | VES | `https://www.dof.gob.mx/index.php?year=2026&month=07&day=10&edicion=VES` | códigos `[]` (negativo) |
| `index_20260715_MAT.html` | `53d54629a11dd696e0ab73fc9b4a100a5cfd04cddcdf44c06f7f07116b6e4959` | 2026-07-15 | MAT | `https://www.dof.gob.mx/index.php?year=2026&month=07&day=15&edicion=MAT` | códigos `[]` (negativo) |
| `nota_5793257_20260710.html` | `005358ef60342dcdc03f08adfe0d5852226b105d1680333e2fc0c3e3d1abc349` | 2026-07-10 | MAT | `https://www.dof.gob.mx/nota_detalle.php?codigo=5793257&fecha=10/07/2026` | 1 empresa: `ACC210823UA5`, ALIANZA CORPORATIVA CAMARENCE, A.C. |
| `nota_5793258_20260710.html` | `31e9cdd9375ecf199ec2e1bbddb95003220db5fd8e431795b7d673128101e5f6` | 2026-07-10 | MAT | `https://www.dof.gob.mx/nota_detalle.php?codigo=5793258&fecha=10/07/2026` | 1 empresa: `APR181217P21`, ASOCIACIÓN PATRONAL REGIÓN ZAMORA, A.C. |
| `nota_5793259_20260710.html` | `8da99fccb588581e23569cd70a2acabf637a71c65932c96c14142b418aaa8b94` | 2026-07-10 | MAT | `https://www.dof.gob.mx/nota_detalle.php?codigo=5793259&fecha=10/07/2026` | 1 empresa: `CSP170608IV8`, CONFEDERACION DE SERVIDORES PUBLICOS DE LOS PODERES DE LOS ESTADOS, MUNICIPIOS E INSTITUCIONES DESCENTRALIZADAS DE LA REPUBLICA MEXICANA |

Conteos validados el 2026-07-23 corriendo el oráculo real (`IndexService::extractCodes`,
`DocumentService::extractCompanies`) en `php:8.4-cli` (Docker, sin red), código del oráculo
en commit `1218d189d0adc19f799cbca2c60216e594203bfa` de este repo.

Barrido de días (jul 2026, ambas ediciones, menciones "49 Bis" en el índice):
10=MAT:3/VES:0 · 13=0/0 · 14=0/0 · 15=0/0 · 16=0/0 · 17=0/0 · 20=0/0 · 21=0/0 · 22=0/0.
En el RANGO BARRIDO (10–22 jul 2026), solo el 10-jul MAT tiene notas 49 Bis; no se
verificaron fechas anteriores al 10-jul (inicio definido del backfill, Story 4.3).

## Realidades del DOF documentadas por estos fixtures (verificadas a nivel bytes)

- **Encoding — LA TRAMPA:** el header HTTP real dice `Content-Type: text/html; charset=UTF-8`,
  el `<meta>` del documento declara `charset=ISO-8859-1` (con un segundo meta `charset=UTF-8`
  COMENTADO justo debajo — cuidado con sniffers regex no comment-aware), y los pocos bytes
  no-ASCII crudos (~17 por archivo: título "Federación", comentarios JS, `acronym`) son
  **UTF-8** (`C3 B3`, cero bytes ISO como `F3`). TODOS los acentos del CONTENIDO (razones
  sociales) van como entidades HTML (`&Oacute;`, `&ntilde;`), inmunes al charset. En vivo,
  jsoup decodifica por el header (UTF-8, correcto); desde archivo local (estos fixtures, sin
  header) caerá al meta ISO-8859-1 y el chrome hará mojibake ("FederaciÃ³n") — las razones
  sociales NO se afectan. Hay además un BOM `EF BB BF` intercalado a MITAD de archivo
  (offset ~89240 en index_MAT, ~71980 en nota_57) que decodificado como ISO rinde "ï»¿".
- **NBSP:** aparece como entidad `&nbsp;` (14/15/13 por nota), NO como byte crudo 0xA0.
- **`<br>` — nuance de paridad:** `<br>` literal = 40/35/47 por nota, `<br />` = 15 por nota.
  El oráculo (`DocumentService.php`: `str_ireplace('<br>', ' ')`) reemplaza SOLO la variante
  literal; los `<br />` quedan intactos en el HTML que parsea. El port Java (Story 1.6) debe
  replicar exactamente eso — reemplazar "todos los br" divergiría en el job de paridad.
- **EOL mixtos:** CRLF y LF conviven en cada archivo (protegidos con `-text` en git).
- **Notas multi-empresa:** NO existen en el rango barrido — cada oficio 49 Bis comunica UNA
  empresa. Cuando el DOF publique una nota con anexo multi-empresa, capturarla con este
  mismo procedimiento y agregarla aquí (DW-7; desviación del AC aprobada en Story 1.2).

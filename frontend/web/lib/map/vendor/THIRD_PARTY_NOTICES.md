# Vendored map runtime dependencies

These files are served locally to avoid executing mutable third-party CDN code in authenticated map pages.

| Component | Version | Source | License |
|---|---:|---|---|
| jQuery | 3.7.1 | `jquery/jquery` / Composer `bower-asset/jquery` | MIT |
| Bootstrap CSS, transition and modal modules | 3.4.1 | `twbs/bootstrap` / Composer `bower-asset/bootstrap` | MIT |
| Mapbox.js | 3.1.1 | Official Mapbox CDN and `mapbox/mapbox.js` | BSD-3-Clause |
| Leaflet.draw | 0.4.10 | npm `leaflet-draw` | MIT |
| Leaflet.markercluster | 1.0.0 | npm `leaflet.markercluster` | MIT |
| Leaflet.Locate | 0.43.0 | npm `leaflet.locatecontrol` | MIT |
| Leaflet-hash | 0.2.1 | npm `leaflet-hash` | BSD-2-Clause |
| Turf helpers, area, circle and points-within-polygon | 7.3.5 | npm `@turf/helpers`, `@turf/area`, `@turf/circle`, `@turf/points-within-polygon` | MIT |
| Font Awesome | 4.7.0 | npm `font-awesome` | Font: SIL OFL 1.1; CSS: MIT |

The Turf bundle contains only the functions used by these views. A local compatibility wrapper exposes `pointsWithinPolygon` as `turf.within` and adapts the current circle options object to the legacy call signature. Only Bootstrap's transition and modal modules are shipped; the vulnerable tooltip, popover, and button modules are not included. License files distributed by the upstream packages are kept beside the corresponding assets where available.

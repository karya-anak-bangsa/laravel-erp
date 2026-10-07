// Hanya bagian ECharts yang dipakai. Import bernama statis di modul ini memungkinkan
// Vite membuang jenis grafik lain; modul ini sendiri dimuat dinamis oleh grafik-kas.js.
import * as echarts from 'echarts/core';
import { BarChart } from 'echarts/charts';
import { GridComponent, LegendComponent, TooltipComponent } from 'echarts/components';
import { CanvasRenderer } from 'echarts/renderers';

echarts.use([BarChart, GridComponent, LegendComponent, TooltipComponent, CanvasRenderer]);

export default echarts;

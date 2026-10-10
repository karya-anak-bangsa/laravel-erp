// Entri JS frontend publik (terpisah dari admin.js).
import { pasangDialogLayanan } from './web/dialog-layanan.js';
import { pasangFormulirKontak } from './web/formulir-kontak.js';
import { pasangMenu } from './web/menu.js';
import { pasangPeta } from './web/peta.js';
import { pasangTema } from './web/tema.js';

pasangTema();
pasangMenu();
pasangDialogLayanan();
pasangFormulirKontak();
pasangPeta();

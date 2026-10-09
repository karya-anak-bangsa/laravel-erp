// Entri JS frontend publik (terpisah dari admin.js).
// Basecoat dibundel sebagai IIFE: tabs.js mendaftar ke window.basecoat, jadi basecoat wajib diimpor lebih dulu.
import 'basecoat-css/basecoat';
import 'basecoat-css/tabs';
import { pasangDialogLayanan } from './web/dialog-layanan.js';
import { pasangFormulirKontak } from './web/formulir-kontak.js';
import { pasangMenu } from './web/menu.js';
import { pasangPeta } from './web/peta.js';
import { pasangPortofolio } from './web/portofolio.js';
import { pasangTema } from './web/tema.js';
import { pasangTombolWa } from './web/tombol-wa.js';

pasangTema();
pasangMenu();
pasangPortofolio();
pasangDialogLayanan();
pasangTombolWa();
pasangFormulirKontak();
pasangPeta();

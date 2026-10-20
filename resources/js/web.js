// Entri JS frontend publik (terpisah dari admin.js).
import { pasangTombolBagikan } from './web/bagikan.js';
import { pasangCarousel } from './web/carousel.js';
import { pasangDialogLayanan } from './web/dialog-layanan.js';
import { pasangTombolKembali } from './web/kembali.js';
import { pasangMenu } from './web/menu.js';
import { pasangTema } from './web/tema.js';

pasangTema();
pasangMenu();
pasangDialogLayanan();
pasangTombolKembali();
pasangTombolBagikan();
pasangCarousel();

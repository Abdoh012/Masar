// Client-side PDF generation for the certificate document. The backend has no
// certificate-download endpoint (its presenter hardcodes can_download=false
// and documents the endpoint as removed), so the PDF is produced from the
// real rendered artifact: html-to-image rasterizes the CertificateDocument DOM
// node (computed styles + embedded self-hosted fonts, incl. the Dancing Script
// signature) into a PNG, and jsPDF places it onto an A4 page sized to keep the
// on-screen layout exact. The node must be mounted in the document (hidden
// off-screen wrapper — see DownloadCertificateButton); fonts are inlined by
// html-to-image from the next/font files, so the PDF matches the browser
// preview.
import { toPng } from "html-to-image";
import { jsPDF } from "jspdf";

// A4 portrait at 96dpi (px). The certificate document is captured at this
// width so the printed scale matches the UI's fixed design.
const A4 = { width: 794, height: 1123 } as const;

// Export margin (px, on the A4 canvas) around the document.
const EXPORT_MARGIN = 40;

// Raster scale — 2× for crisp text in the PNG that jsPDF downsamples.
const PIXEL_RATIO = 2;

export async function downloadElementAsPdf(
  element: HTMLElement,
  filename: string,
): Promise<void> {
  // The signature line uses the Dancing Script font (self-hosted via
  // next/font); make sure it is fully loaded before rasterizing so the
  // embedded fonts in the PDF match the rendered preview.
  await document.fonts.ready;

  // Guard against capturing a node that hasn't actually laid out — html-to-image
  // sizes the canvas from the node's offset size, so a zero-size/offscreen host
  // silently yields a blank PDF. Fail loudly instead of exporting an empty file.
  if (!element.offsetWidth || !element.offsetHeight) {
    throw new Error("Certificate has no rendered size to capture.");
  }

  const dataUrl = await toPng(element, {
    pixelRatio: PIXEL_RATIO,
    cacheBust: true,
    backgroundColor: "#ffffff",
  });

  const image = new Image();
  await new Promise<void>((resolve, reject) => {
    image.onload = () => resolve();
    image.onerror = () => reject(new Error("Failed to rasterize certificate."));
    image.src = dataUrl;
  });

  // Logical (CSS-pixel) size of the captured document.
  const logicalWidth = image.naturalWidth / PIXEL_RATIO;
  const logicalHeight = image.naturalHeight / PIXEL_RATIO;

  // Fit the document onto the A4 canvas, preserving its aspect ratio.
  const scale = Math.min(
    (A4.width - EXPORT_MARGIN * 2) / logicalWidth,
    (A4.height - EXPORT_MARGIN * 2) / logicalHeight,
  );

  const drawWidth = logicalWidth * scale;
  const drawHeight = logicalHeight * scale;
  const drawX = (A4.width - drawWidth) / 2;
  const drawY = (A4.height - drawHeight) / 2;

  const pdf = new jsPDF({
    orientation: "portrait",
    unit: "px",
    format: [A4.width, A4.height],
    compress: true,
  });

  pdf.addImage(dataUrl, "PNG", drawX, drawY, drawWidth, drawHeight, undefined, "FAST");
  pdf.save(filename);
}
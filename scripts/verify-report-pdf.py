"""Read-only PDF layout verification. Requires pdfplumber, pypdfium2 and Pillow."""
import argparse
from pathlib import Path

import pdfplumber
import pypdfium2 as pdfium
from PIL import ImageChops

parser = argparse.ArgumentParser()
parser.add_argument('pdf', type=Path)
parser.add_argument('--template', type=Path, default=Path('resources/reports/ar_blank.pdf'))
parser.add_argument('--render-dir', type=Path, default=Path('tmp/pdfs/verified'))
args = parser.parse_args()
args.render_dir.mkdir(parents=True, exist_ok=True)
source = pdfium.PdfDocument(args.template)
original = source[0].render(scale=2).to_pil().convert('RGB')
document = pdfium.PdfDocument(args.pdf)

with pdfplumber.open(args.pdf) as metadata:
    for index, page in enumerate(metadata.pages):
        assert abs(page.width - 595.44) < .01 and abs(page.height - 841.68) < .01, 'Paper size changed'
        rendered = document[index].render(scale=2).to_pil().convert('RGB')
        rendered.save(args.render_dir / f'{args.pdf.stem}-{index + 1}.png')
        assert ImageChops.difference(original.crop((0, 0, original.width, 214)), rendered.crop((0, 0, rendered.width, 214))).getbbox() is None, 'Header differs'
        # Compare every sidebar pixel except the explicitly permitted page-number area.
        diff = ImageChops.difference(original.crop((0, 0, 204, original.height)), rendered.crop((0, 0, 204, rendered.height)))
        if len(document) > 1:
            diff.paste((0, 0, 0), (106, 1520, 170, 1542))
        assert diff.getbbox() is None, 'Sidebar differs outside page numbering'
        text = page.extract_text()
        assert 'Powered by TCPDF' not in text, 'Unexpected added footer'
        if len(document) > 1:
            assert f'Page {index + 1} of {len(document)}' in text, 'Incorrect page number'
        body = [c for c in page.chars if c['text'].strip() and c['x0'] > 100 and c['top'] > 107]
        assert body, 'Report body missing'
        for char in body:
            assert char['x0'] >= 107.9 and char['x1'] <= 562.1, 'Body crosses horizontal margin'
            assert char['bottom'] <= 770, 'Body crosses bottom margin'
            if 'Arial' in char['fontname']:
                assert abs(char['size'] - 11.04) < .01, 'Accomplishment font size changed'
            else:
                assert 'Cambria' in char['fontname'] and abs(char['size'] - 12) < .01, 'Body font changed'
        print(f'Page {index + 1}: original header/sidebar preserved; fonts, dimensions and margins verified.')
print(f'PASS: {len(document)} page(s). Rendered pages: {args.render_dir}')

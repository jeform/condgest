ADDFONT V1.0
Author: Yann SUGERE

Installation:

Uncompress all the files in the makefont directory of FPDF.

Usage and restrictions:

This script works on Windows only and can't directly import fonts located in the
Windows/Fonts system directory. In order to import them, you have to copy them in another
directory first.

Using the script is simple: just follow what's written on the screen.

After you have processed your fonts, don't forget to put in your PDF generating script:

$pdf->AddFont('name_of_the_font');

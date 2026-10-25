import 'package:cartzy/services/government_id_parser.dart';
import 'package:flutter_test/flutter_test.dart';

void main() {
  test('PhilSys national ID', () {
    const text = '''
REPUBLIKA NG PILIPINAS
Republic of the Philippines
PAMBANSANG PAGKAKAKILANLAN
Philippine Identification Card
1234-5678-9012-3456
Apelyido/Last Name
DELA CRUZ
Mga Pangalan/Given Names
JUAN MIGUEL
Gitnang Apelyido/Middle Name
SANTOS
Petsa ng Kapanganakan/Date of Birth
JANUARY 15, 1995
''';
    final f = GovernmentIdParser.parse(text);
    expect(f.idType, GovernmentIdParser.philSys);
    expect(f.idNumber, '1234-5678-9012-3456');
    expect(f.lastName, 'Dela Cruz');
    expect(f.firstName, 'Juan Miguel');
    expect(f.birthday, DateTime(1995, 1, 15));
  });

  test("driver's license with combined name line", () {
    const text = '''
REPUBLIC OF THE PHILIPPINES
DEPARTMENT OF TRANSPORTATION
LAND TRANSPORTATION OFFICE
NON-PROFESSIONAL DRIVER'S LICENSE
Last Name, First Name, Middle Name
REYES, MARIA CLARA BAUTISTA
Nationality Sex Date of Birth Weight (kg) Height(m)
PHL F 1998/03/07 52 1.58
License No. Expiration Date Agency Code
N03-12-345678 2030/03/07 N32
''';
    final f = GovernmentIdParser.parse(text);
    expect(f.idType, GovernmentIdParser.driversLicense);
    expect(f.idNumber, 'N03-12-345678');
    expect(f.lastName, 'Reyes');
    expect(f.firstName, 'Maria Clara Bautista');
    expect(f.birthday, DateTime(1998, 3, 7));
  });

  test('passport machine-readable lines', () {
    const text = '''
PASAPORTE / PASSPORT
REPUBLIKA NG PILIPINAS
P<PHLDELA<CRUZ<<JUAN<PROTACIO<<<<<<<<<<<<<<<
P1234567A5PHL9001011M3001012<<<<<<<<<<<<<<04
''';
    final f = GovernmentIdParser.parse(text);
    expect(f.idType, GovernmentIdParser.passport);
    expect(f.idNumber, 'P1234567A');
    expect(f.lastName, 'Dela Cruz');
    expect(f.firstName, 'Juan Protacio');
  });

  test('UMID', () {
    const text = '''
UNIFIED MULTI-PURPOSE ID
CRN-0111-2345678-9
SURNAME
SANTOS
GIVEN NAME
ANA
MIDDLE NAME
LOPEZ
DATE OF BIRTH
1990/12/25
''';
    final f = GovernmentIdParser.parse(text);
    expect(f.idType, GovernmentIdParser.umid);
    expect(f.idNumber, '0111-2345678-9');
    expect(f.lastName, 'Santos');
    expect(f.firstName, 'Ana');
    expect(f.birthday, DateTime(1990, 12, 25));
  });

  test('falls back to the ID type the rider picked', () {
    const text = 'SSS\n34-1234567-8\nJOSE RIZAL';
    final f = GovernmentIdParser.parse(text, preferredType: GovernmentIdParser.sss);
    expect(f.idType, GovernmentIdParser.sss);
    expect(f.idNumber, '34-1234567-8');
  });

  test('unreadable text gives no fields', () {
    final f = GovernmentIdParser.parse('blurry ### ~~');
    expect(f.isEmpty, isTrue);
  });
}

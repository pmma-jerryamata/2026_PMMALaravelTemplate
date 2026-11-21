@extends('layouts.registrar')

@section('title', 'Frequently Asked Questions')

@section('heading')
    <section class="support-intro registrar-intro" aria-labelledby="faq-heading">
        <div class="container">
            <div>
                <h1 id="faq-heading">Frequently Asked Questions</h1>
            </div>
        </div>
    </section>
@endsection

@section('content')
    <div class="faq-container registrar-faq">
        <details class="portal-faq" name="registrar-faq">
            <summary>What courses does the Philippine Merchant Marine Academy offer?</summary>
            <div class="registrar-faq-answer">
                <p>PMMA offers two baccalaureate programs:</p>
                <ul>
                    <li>Bachelor of Science in Marine Transportation (BSMT)</li>
                    <li>Bachelor of Science in Marine Engineering (BSMar-E)</li>
                </ul>
                <p>It also offers two graduate programs:</p>
                <ul>
                    <li>Master in Maritime Education and Training (MMET)</li>
                    <li>Master in Shipping Management (MSM)</li>
                </ul>
            </div>
        </details>

        <details class="portal-faq" name="registrar-faq">
            <summary>Does PMMA have its own Training Center?</summary>
            <div class="registrar-faq-answer">
                <p>Yes. The PMMA Training Center is at the PMMA Complex in San Narciso, Zambales. Its courses include:</p>
                <ul>
                    <li>Basic Training</li>
                    <li>Refresher Course on Basic Training</li>
                    <li>Advanced Fire Fighting</li>
                </ul>
                <p>Call the Training Center at <a href="tel:+63479133104">(047) 913-3104</a> to confirm the venue for your course.</p>
            </div>
        </details>

        <details class="portal-faq" name="registrar-faq">
            <summary>When is the PMMA Annual Entrance Examination?</summary>
            <div class="registrar-faq-answer">
                <p>PMMA scheduled the entrance examination for Academic Year 2027–2028 on <time datetime="2026-11-28">Saturday, November 28, 2026</time>, at designated testing centers nationwide.</p>
                <ul>
                    <li><a target="_blank" rel="noopener" href="https://pmma.edu.ph/admission-process/application-procedure/">Application procedure</a></li>
                    <li><a target="_blank" rel="noopener" href="https://pmma.edu.ph/pmma-testing-centers/">PMMA testing centers</a></li>
                    <li><a target="_blank" rel="noopener" href="https://caraga.deped.gov.ph/publications/issuances/rm-no-1068-s-2026-conduct-of-the-philippine-merchant-marine-academy-pmma-annual-entrance-examination-for-school-year-2027-2028/pdf">Examination memorandum for AY 2027–2028</a></li>
                </ul>
            </div>
        </details>

        <details class="portal-faq" name="registrar-faq">
            <summary>What are the requirements to qualify for the PMMA Entrance Examination?</summary>
            <div class="registrar-faq-answer">
                <p>Review the <a target="_blank" rel="noopener" href="{{ route('qualifications') }}">PMMA qualification requirements</a> and follow the application instructions for your intake on the <a target="_blank" rel="noopener" href="https://admissions.pmma.edu.ph/">PMMA Admissions Portal</a>. Ask the Admissions Office about accepted submission methods and deadlines.</p>
            </div>
        </details>

        <details class="portal-faq" name="registrar-faq">
            <summary>What should I review for the PMMA Annual Entrance Examination?</summary>
            <div class="registrar-faq-answer">
                <p>The aptitude test covers four subject areas:</p>
                <ul>
                    <li>English (reading comprehension, grammar, and vocabulary)</li>
                    <li>High school mathematics (algebra, geometry, statistics, and trigonometry)</li>
                    <li>Science (general science, chemistry, and physics)</li>
                    <li>Abstract reasoning</li>
                </ul>
            </div>
        </details>

        <details class="portal-faq" name="registrar-faq">
            <summary>How will I know if my application was approved?</summary>
            <div class="registrar-faq-answer">
                <p>Sign in to the <a target="_blank" rel="noopener" href="https://admissions.pmma.edu.ph/">PMMA Admissions Portal</a> to check your application and look for your test permit. For help, email <a href="mailto:admissions@pmma.edu.ph">admissions@pmma.edu.ph</a> or call <a href="tel:+63479132239">(047) 913-2239</a>.</p>
            </div>
        </details>

        <details class="portal-faq" name="registrar-faq">
            <summary>Is there an entrance examination fee?</summary>
            <div class="registrar-faq-answer">
                <p>The <a target="_blank" rel="noopener" href="https://www.facebook.com/pmmaadmissions/">PMMA Admissions Office</a> announced a non-refundable ₱500 application fee for AY 2027–2028 (Class of 2031). Check the <a target="_blank" rel="noopener" href="https://admissions.pmma.edu.ph/">PMMA Admissions Portal</a> for the fee and payment instructions for your intake.</p>
            </div>
        </details>

        <details class="portal-faq" name="registrar-faq">
            <summary>Is there a limit to how many times I can take the exam?</summary>
            <div class="registrar-faq-answer">
                <p>Eligibility to apply again depends on meeting the requirements for the new intake, including its age limit. Check the <a target="_blank" rel="noopener" href="{{ route('qualifications') }}">qualification requirements</a> and ask Admissions about the applicable cutoff date and repeat applications.</p>
            </div>
        </details>

        <details class="portal-faq" name="registrar-faq">
            <summary>What should I prepare for the exam?</summary>
            <div class="registrar-faq-answer">
                <p>Bring your test permit and a valid ID. Follow the reporting time, venue, and list of permitted items on your test permit or the exam advisory for your intake.</p>
            </div>
        </details>

        <details class="portal-faq" name="registrar-faq">
            <summary>Can I go straight to a testing center as a walk-in applicant?</summary>
            <div class="registrar-faq-answer">
                <p>Check with the <a href="mailto:admissions@pmma.edu.ph">PMMA Admissions Office</a> before going to a testing center. If walk-ins are accepted for your intake, confirm the reporting time, available slots, and documents you need to bring.</p>
            </div>
        </details>

        <details class="portal-faq" name="registrar-faq">
            <summary>How will I know if I passed the PMMA Entrance Examination?</summary>
            <div class="registrar-faq-answer">
                <p>Check the <a target="_blank" rel="noopener" href="https://admissions.pmma.edu.ph/">PMMA Admissions Portal</a> and look for results announcements on the <a target="_blank" rel="noopener" href="https://pmma.edu.ph/">official PMMA website</a> and the <a target="_blank" rel="noopener" href="https://www.facebook.com/PMMAOFFICIAL/">official PMMA Facebook page</a>.</p>
            </div>
        </details>
    </div>
@endsection

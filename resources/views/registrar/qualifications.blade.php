@extends('layouts.registrar')

@section('title', 'Qualification Requirements')

@section('heading')
    <section class="support-intro registrar-intro" aria-labelledby="qualifications-heading">
        <div class="container">
            <div>
                <h1 id="qualifications-heading">Qualification Requirements</h1>
            </div>
        </div>
    </section>
@endsection

@section('content')
    <div class="faq-container registrar-faq registrar-qualifications">
        <p>This checklist is based on the <a href="https://web.archive.org/web/20250317191212/https://pmma.edu.ph/qualification-requirements/">archived PMMA qualification requirements</a>. Confirm the requirements for your intake with the <a href="mailto:admissions@pmma.edu.ph">PMMA Admissions Office</a>.</p>

        <section class="portal-panel" aria-labelledby="basic-qualifications">
            <h2 id="basic-qualifications">Basic Qualification Requirements</h2>
            <ol>
                <li>Male or female, physically fit, and a natural-born Filipino citizen.</li>
                <li>Single, never married, and has no children out of wedlock.</li>
                <li>Free from any accusation or conviction for a crime against the law and the state.</li>
                <li>At least 17 years old and no more than 23 years old upon enrollment on August 1.</li>
                <li>A Grade 12 student or at college level.</li>
                <li>At least 160 cm (5 ft. 3 in.) for male applicants and 155 cm (5 ft. 1 in.) for female applicants.</li>
                <li>A body mass index (BMI) below 25.</li>
                <li>Good academic performance.</li>
                <li>Has never enrolled in PMMA.</li>
            </ol>
        </section>

        <section class="portal-panel" aria-labelledby="disqualification-criteria">
            <h2 id="disqualification-criteria">Disqualification Criteria for Incoming Probationary Midshipmen and Midshipwomen</h2>
            <h3>Medical Disqualifications</h3>

            <details class="portal-faq" name="registrar-qualifications">
                <summary>For Male Applicants</summary>
                <div class="registrar-faq-answer">
                    <ol>
                        <li>Undescended testicles.</li>
                        <li>Absence of one or both testicles.</li>
                        <li>Suspicious enlargement of the testis or epididymis.</li>
                        <li>Varicocele.</li>
                        <li>Redundant prepuce.</li>
                        <li>Height below 5 ft. 3 in.</li>
                    </ol>
                </div>
            </details>

            <details class="portal-faq" name="registrar-qualifications">
                <summary>For Female Applicants</summary>
                <div class="registrar-faq-answer">
                    <ol>
                        <li>History of childbirth or lactation.</li>
                        <li>Pregnancy.</li>
                        <li>Irregular menstrual cycle, including menorrhagia.</li>
                        <li>Dysmenorrhea severe enough to cause recurring absences.</li>
                        <li>Persistent ovarian cyst considered clinically significant.</li>
                        <li>New growth of the external or internal genitalia, such as uterine myoma.</li>
                        <li>Breast mass.</li>
                        <li>Endometriosis unless surgically eradicated or controlled.</li>
                        <li>Acute or chronic cervicitis.</li>
                        <li>Generalized enlargement of the uterus due to any cause.</li>
                        <li>Cervical polyps.</li>
                        <li>Height below 5 ft. 1 in.</li>
                    </ol>
                </div>
            </details>

            <details class="portal-faq" name="registrar-qualifications">
                <summary>For Male and Female Applicants</summary>
                <div class="registrar-faq-answer">
                    <ol>
                        <li>Lordosis, kyphosis, or thoracic or lumbar scoliosis, regardless of angle.</li>
                        <li>Severe bowlegs or knock knees.</li>
                        <li>Severe pes planus (flat feet).</li>
                        <li>Acute or subacute arthritis.</li>
                        <li>Underweight or overweight. Applicants must have a BMI below 25 at the physical examination.</li>
                        <li>Severe bacterial, fungal, or viral skin disease, or excessive acne. The checklist requires warts and other skin diseases to be resolved before the medical examination.</li>
                        <li>Leprosy in any form.</li>
                        <li>Tattoos or hypertrophic tattoo scars causing disfigurement, or extensive keloid scars, especially on the face.</li>
                        <li>Gross physical deformity or asymmetry, whether congenital or caused by trauma or disease, including extra or missing appendages such as fingers.</li>
                        <li>Refractive errors (nearsightedness, farsightedness, or astigmatism), crossed eyes, and other ocular disorders.</li>
                        <li>Visual acuity below 20/30 OU (both eyes).</li>
                        <li>Use of contact lenses or eyeglasses.</li>
                        <li>Defective color perception. The Ishihara test requires a perfect score for BSMT and allows up to two errors for BSMar-E.</li>
                        <li>Deafness or a perforated eardrum.</li>
                        <li>Severe or chronic otitis media.</li>
                        <li>Impacted cerumen (earwax), which must be removed before the medical examination.</li>
                        <li>Chronic sinusitis or nasal polyps.</li>
                        <li>Hypertension, abnormal ECG findings (such as AV blocks, bundle branch blocks, ischemia, infarctions, arrhythmia, tachycardia, or bradycardia), and other heart abnormalities or diseases.</li>
                        <li>Active pulmonary tuberculosis, massive pulmonary fibrosis, pulmonary infarctions, pneumonitis, bronchopneumonia, atelectasis, or other lung diseases.</li>
                        <li>History of asthma.</li>
                        <li>Lymphadenopathy.</li>
                        <li>Epilepsy, seizures, syncope, or vertigo.</li>
                        <li>Anemia or blood dyscrasias.</li>
                        <li>Hyperthyroidism (goiter) or hypothyroidism.</li>
                        <li>Diabetes mellitus.</li>
                        <li>Active or inactive hepatitis B.</li>
                        <li>Gallstones.</li>
                        <li>Chronic gastritis, peptic ulcer disease, duodenal ulcers, or other gastrointestinal conditions.</li>
                        <li>Major surgery within the preceding six months.</li>
                        <li>External or internal hemorrhoids, fissures, or fistula-in-ano.</li>
                        <li>Old or recent fractures with internal fixation plates or screws still in place and susceptible to trauma.</li>
                        <li>Sexually transmitted infections such as gonorrhea, nongonococcal urethritis, or syphilis.</li>
                        <li>Hernia.</li>
                        <li>Kidney disease or kidney stones.</li>
                        <li>Current or maintenance medication, including for hypertension, asthma, epilepsy, seizures, syncope, or vertigo. Ask PMMA Admissions and the screening clinician how this item applies to your prescriptions.</li>
                    </ol>
                    <p><a href="https://elibrary.judiciary.gov.ph/thebookshelf/showdocs/2/87423">Republic Act No. 11166, Section 49(b)</a> prohibits refusal of admission based wholly or partly on actual, perceived, or suspected HIV status.</p>
                </div>
            </details>

            <h3>Dental Disqualifications</h3>
            <ol>
                <li>Cleft palate and cleft lip.</li>
                <li>Malocclusion, including Class II, Class III, or lateral crossbite.</li>
                <li>Open bite.</li>
                <li>Tongue-tie that may interfere with speech, breathing, swallowing, or eating.</li>
                <li>Four missing anterior or posterior teeth (front teeth or molars).</li>
                <li>Complete upper or lower denture.</li>
                <li>One or more impacted teeth.</li>
                <li>Facial asymmetry.</li>
                <li>Orthodontic brackets or other orthodontic appliances.</li>
                <li>Oral tumors or cysts.</li>
            </ol>
        </section>
    </div>
@endsection

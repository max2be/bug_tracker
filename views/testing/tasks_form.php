<?php include APP_ROOT . '/views/layout/errors.php'; ?>

<div class="row g-3 mb-4">
    <div class="col-md-6">
        <label class="form-label">Code задачи</label>
        <input type="text" name="code" class="form-control" value="<?= e($form['code']) ?>" placeholder="KARMADEV-999999" required>
    </div>
    <div class="col-md-6">
        <label class="form-label">Активна</label>
        <select name="is_active" class="form-select">
            <option value="1" <?= selected($form['is_active'], '1') ?>>Да</option>
            <option value="0" <?= selected($form['is_active'], '0') ?>>Нет</option>
        </select>
    </div>
</div>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="mb-0">Вопросы</h3>
    <button type="button" class="btn btn-outline-primary" id="add-question">Добавить вопрос</button>
</div>

<div id="questions">
    <?php foreach ($questions as $questionIndex => $question): ?>
        <div class="question-card">
            <div class="mb-3">
                <label class="form-label">Текст вопроса</label>
                <textarea class="form-control" name="questions[<?= e($questionIndex) ?>][text]" rows="3" required><?= e($question['text']) ?></textarea>
            </div>
            <div class="answers">
                <?php foreach ($question['answers'] as $answerIndex => $answer): ?>
                    <?php
                    $answerText = is_array($answer) ? (string) ($answer['text'] ?? '') : (string) $answer;
                    $isCorrect = is_array($answer)
                        ? !empty($answer['is_correct'])
                        : ((int) ($question['correctIndex'] ?? -1) === $answerIndex);
                    ?>
                    <div class="answer-row">
                        <input type="radio" name="questions[<?= e($questionIndex) ?>][correctIndex]" value="<?= e($answerIndex) ?>" <?= $isCorrect ? 'checked' : '' ?> required>
                        <input type="text" class="form-control" name="questions[<?= e($questionIndex) ?>][answers][<?= e($answerIndex) ?>][text]" value="<?= e($answerText) ?>" placeholder="Вариант ответа" required>
                        <button type="button" class="btn btn-outline-danger btn-sm remove-answer">Удалить вариант</button>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-outline-secondary btn-sm add-answer">Добавить вариант</button>
                <button type="button" class="btn btn-danger btn-sm remove-question">Удалить вопрос</button>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<template id="question-template">
    <div class="question-card">
        <div class="mb-3">
            <label class="form-label">Текст вопроса</label>
            <textarea class="form-control" rows="3" required></textarea>
        </div>
        <div class="answers"></div>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-outline-secondary btn-sm add-answer">Добавить вариант</button>
            <button type="button" class="btn btn-danger btn-sm remove-question">Удалить вопрос</button>
        </div>
    </div>
</template>

<script>
const questionsContainer = document.getElementById('questions');
const questionTemplate = document.getElementById('question-template');
const addQuestionButton = document.getElementById('add-question');

function createTestAnswerRow(questionIndex, answerIndex, value = '', checked = false) {
    const row = document.createElement('div');
    row.className = 'answer-row';
    row.innerHTML = `
        <input type="radio" name="questions[${questionIndex}][correctIndex]" value="${answerIndex}" ${checked ? 'checked' : ''} required>
        <input type="text" class="form-control" name="questions[${questionIndex}][answers][${answerIndex}][text]" value="${value.replace(/"/g, '&quot;')}" placeholder="Вариант ответа" required>
        <button type="button" class="btn btn-outline-danger btn-sm remove-answer">Удалить вариант</button>
    `;
    return row;
}

function reindexTestQuestions() {
    const questionBlocks = questionsContainer.querySelectorAll('.question-card');

    questionBlocks.forEach((block, questionIndex) => {
        block.querySelector('textarea').name = `questions[${questionIndex}][text]`;

        block.querySelectorAll('.answer-row').forEach((row, answerIndex) => {
            row.querySelector('input[type="radio"]').name = `questions[${questionIndex}][correctIndex]`;
            row.querySelector('input[type="radio"]').value = answerIndex;
            row.querySelector('input[type="text"]').name = `questions[${questionIndex}][answers][${answerIndex}][text]`;
        });
    });
}

function addTestQuestion() {
    const fragment = questionTemplate.content.cloneNode(true);
    const questionBlock = fragment.querySelector('.question-card');
    const answersContainer = questionBlock.querySelector('.answers');
    answersContainer.appendChild(createTestAnswerRow(0, 0, '', true));
    answersContainer.appendChild(createTestAnswerRow(0, 1, '', false));
    questionsContainer.appendChild(fragment);
    reindexTestQuestions();
}

addQuestionButton.addEventListener('click', addTestQuestion);

questionsContainer.addEventListener('click', (event) => {
    if (event.target.classList.contains('add-answer')) {
        const questionBlock = event.target.closest('.question-card');
        const answersContainer = questionBlock.querySelector('.answers');
        const questionIndex = Array.from(questionsContainer.children).indexOf(questionBlock);
        const answerIndex = answersContainer.querySelectorAll('.answer-row').length;
        answersContainer.appendChild(createTestAnswerRow(questionIndex, answerIndex));
        reindexTestQuestions();
    }

    if (event.target.classList.contains('remove-answer')) {
        const answersContainer = event.target.closest('.answers');
        if (answersContainer.querySelectorAll('.answer-row').length <= 2) {
            alert('У вопроса должно остаться минимум 2 варианта ответа.');
            return;
        }

        event.target.closest('.answer-row').remove();
        if (!answersContainer.querySelector('input[type="radio"]:checked')) {
            const firstRadio = answersContainer.querySelector('input[type="radio"]');
            if (firstRadio) {
                firstRadio.checked = true;
            }
        }
        reindexTestQuestions();
    }

    if (event.target.classList.contains('remove-question')) {
        event.target.closest('.question-card').remove();
        reindexTestQuestions();
    }
});

if (!questionsContainer.children.length) {
    addTestQuestion();
} else {
    reindexTestQuestions();
}
</script>

# SOLUTIONS

## Tasks

### ERD Diagram
![img.png](img.png)

The entities keep track of questions and answers for a user as they complete assessments. 
The user takes an invitation token and takes part in an assessment where they are given a selection of questions, 
the system then gives the test maker a selection of answer types either one of a given options or text or a number.

The answer the test taker gives is stored in assessment answer entity.

### Understand Scoring

started by setting up postman on my device and reading through the response for d1111111-1111-1111-1111-111111111111

The current percentage given for the element is 53.85 as described in the Phase 2 document. (obtained by using postman)

```
{
    "instance": {
        "id": "d1111111-1111-1111-1111-111111111111",
        "created_at": "2026-02-28 15:18:03",
        "updated_at": "2026-02-28 15:18:03",
        "completed": false,
        "completed_at": null,
        "responder_name": "Test Teacher",
        "element": "1.1"
    },
    "total_questions": 4,
    "answered_questions": 2,
    "completion_percentage": 50,
    "scores": {
        "element": "1.1",
        "total_score": 9,
        "max_score": 15,
        "percentage": 53.85
    },
    "element_scores": {
        "1.1": {
            "element": "1.1",
            "total_questions": 4,
            "answered_questions": 2,
            "completion_percentage": 50,
            "scores": {
                "total_score": 9,
                "max_score": 15,
                "percentage": 53.85
            },
            "question_answers": [
                {
                    "question_id": "a1111111-1111-1111-1111-111111111111",
                    "question_title": "How confident are you in planning engaging lessons?",
                    "question_suite": null,
                    "question_sequence": 1,
                    "is_reflection": false,
                    "reflection_prompt": null,
                    "element": "1.1",
                    "max_score": 5,
                    "is_answered": true,
                    "answer_id": "e1111111-1111-1111-1111-111111111111",
                    "answer_value": 4,
                    "answer_text": "Very confident",
                    "answer_option_id": "b1111111-1111-1111-1111-111111111114",
                    "text_answer": null,
                    "numeric_value": null,
                    "answer_explanation": null,
                    "option_number": 4
                }, ...
            ]
        }
    },
    "insights": [
        {
            "type": "completion",
            "message": "You have 2 questions remaining to complete this assessment.",
            "positive": false
        },
        {
            "type": "performance",
            "message": "You demonstrate strong confidence in this element of teaching practice.",
            "positive": true
        }
    ]
}
```
The normalised percentage is the current percentage of the test taking into account the fact the minimum score is 1.
Therefore, to get a correct percentage you would need to -1 from all the answers to give the correct percentage.

However, there is a bug with the current system. In the event of the assessment being completed the 4th question 
would still be added to the elementAddedQuestion and therefore the percentage given back would become wrong.
So, I am adding an if statement to check if the question is worth points.

I could add a check and add the minimum value for the current percentage (1) if the question has not been answered.
However, I feel as if this is incorrect to do.


## Answers endpoint

### overview

My task list for this project was first planning it out mentally, then starting with what I can see / what will be 
displayed. I created the controller first, making a template out and testing to see if it responded correctly. Then 
moved onto the new service, I created a basic outline of this as well and connected it up to the controller requiring
minor changes to the services.yaml file. Then I created the new repositories for each of the entities I would be
Interacting with. I then spent time connecting them up into the services and controller. I then spent some time debugging
realising my implementation for the if item exists already was incorrect so spent time fixing this. Then lastly
added the entity management.

I then spent some additional time bugfixing because I wrote "likert" as "Linkert".

Overall my strategy was to work from what i could see on postman 
(at the moment my ability to debug is limited by my setup)

### explanations for choices

repositories for each entity: this is I believe the better option over continuing the original repository because it is
more readable for humans in addition for allowing symfony to utilise it's caching capabilities.

some repositories in controllers others in service layer: The way I was taught controllers should be kept as simple
as possible only doing basic checks (if item exists), as to reduce the clutter when codebases get larger. Therfore,
I have only kept simple logic checks for if the question / instance exists.


### Test result

```
{
    "instance": {
        "id": "d1111111-1111-1111-1111-111111111111",
        "created_at": "2026-02-28 15:18:03",
        "updated_at": "2026-02-28 15:18:03",
        "completed": false,
        "completed_at": null,
        "responder_name": "Test Teacher",
        "element": "1.1"
    },
    "total_questions": 4,
    "answered_questions": 3,
    "completion_percentage": 75,
    "scores": {
        "element": "1.1",
        "total_score": 12,
        "max_score": 15,
        "percentage": 75
    },
    "element_scores": {
        "1.1": {
            "element": "1.1",
            "total_questions": 4,
            "answered_questions": 3,
            "completion_percentage": 75,
            "scores": {
                "total_score": 12,
                "max_score": 15,
                "percentage": 75
            },
            "question_answers": [
                {
                    "question_id": "a1111111-1111-1111-1111-111111111111",
                    "question_title": "How confident are you in planning engaging lessons?",
                    "question_suite": null,
                    "question_sequence": 1,
                    "is_reflection": false,
                    "reflection_prompt": null,
                    "element": "1.1",
                    "max_score": 5,
                    "is_answered": true,
                    "answer_id": "e1111111-1111-1111-1111-111111111111",
                    "answer_value": 4,
                    "answer_text": "Very confident",
                    "answer_option_id": "b1111111-1111-1111-1111-111111111114",
                    "text_answer": null,
                    "numeric_value": null,
                    "answer_explanation": null,
                    "option_number": 4
                },
                {
                    "question_id": "a2222222-2222-2222-2222-222222222222",
                    "question_title": "How often do you use formative assessment strategies?",
                    "question_suite": null,
                    "question_sequence": 2,
                    "is_reflection": false,
                    "reflection_prompt": null,
                    "element": "1.1",
                    "max_score": 5,
                    "is_answered": true,
                    "answer_id": "e2222222-2222-2222-2222-222222222222",
                    "answer_value": 5,
                    "answer_text": "Always",
                    "answer_option_id": "b2222222-2222-2222-2222-222222222225",
                    "text_answer": null,
                    "numeric_value": null,
                    "answer_explanation": null,
                    "option_number": 5
                },
                {
                    "question_id": "a3333333-3333-3333-3333-333333333333",
                    "question_title": "To what extent do you differentiate instruction for diverse learners?",
                    "question_suite": null,
                    "question_sequence": 3,
                    "is_reflection": false,
                    "reflection_prompt": null,
                    "element": "1.1",
                    "max_score": 5,
                    "is_answered": true,
                    "answer_id": "c60e6848-dfc7-4a42-8f68-a4a49d5244a1",
                    "answer_value": 3,
                    "answer_text": "To some extent",
                    "answer_option_id": "b3333333-3333-3333-3333-333333333333",
                    "text_answer": null,
                    "numeric_value": null,
                    "answer_explanation": null,
                    "option_number": 3
                },
                {
                    "question_id": "a4444444-4444-4444-4444-444444444444",
                    "question_title": "Reflection",
                    "question_suite": null,
                    "question_sequence": 4,
                    "is_reflection": true,
                    "reflection_prompt": "What is one area you would like to develop further in your teaching practice?",
                    "element": "1.1",
                    "max_score": 0,
                    "is_answered": false,
                    "answer_id": null,
                    "answer_value": null,
                    "answer_text": null,
                    "answer_option_id": null,
                    "text_answer": null,
                    "numeric_value": null
                }
            ]
        }
    },
    "insights": [
        {
            "type": "completion",
            "message": "You have 1 questions remaining to complete this assessment.",
            "positive": false
        },
        {
            "type": "performance",
            "message": "You demonstrate strong confidence in this element of teaching practice.",
            "positive": true
        }
    ]
}
```

## Resources used

 - [lucid chart](https://lucid.app/lucidchart/91bdae77-6576-4076-84f1-56741071f269/edit?beaconFlowId=6ACFC8B2839F36AA&invitationId=inv_581de3b8-b586-4c44-99f2-c6885d20bc34&page=0_0#)
 - Postman
 - chat GPT (used mostly for the Assessment sql as on my current system I don't have everything setup)
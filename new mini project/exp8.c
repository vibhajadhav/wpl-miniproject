#include <stdio.h>

#define MAX_PROCESSES 10
#define MAX_RESOURCES 10

int available[MAX_RESOURCES];
int max[MAX_PROCESSES][MAX_RESOURCES];
int allocation[MAX_PROCESSES][MAX_RESOURCES];
int need[MAX_PROCESSES][MAX_RESOURCES];
int n, m;

void calculateNeed() {
    for (int i = 0; i < n; i++) {
        for (int j = 0; j < m; j++) {
            need[i][j] = max[i][j] - allocation[i][j];
        }
    }
}

int isSafe() {
    int work[MAX_RESOURCES], finish[MAX_PROCESSES] = {0};
    for (int i = 0; i < m; i++) {
        work[i] = available[i];
    }
    int safeSequence[MAX_PROCESSES], count = 0;
    
    while (count < n) {
        int found = 0;
        for (int i = 0; i < n; i++) {
            if (!finish[i]) {
                int j;
                for (j = 0; j < m; j++) {
                    if (need[i][j] > work[j]) {
                        break;
                    }
                }
                if (j == m) {
                    for (int k = 0; k < m; k++) {
                        work[k] += allocation[i][k];
                    }
                    safeSequence[count++] = i;
                    finish[i] = 1;
                    found = 1;
                }
            }
        }
        if (!found) {
            return 0;
        }
    }
    printf("System is in a safe state. Safe sequence: ");
    for (int i = 0; i < n; i++) {
        printf("P%d ", safeSequence[i]);
    }
    printf("\n");
    return 1;
}

int main() {
    printf("Enter number of processes and resource types: ");
    scanf("%d %d", &n, &m);
    
    printf("Enter available resources: ");
    for (int i = 0; i < m; i++) {
        scanf("%d", &available[i]);
    }
    
    printf("Enter max resources matrix: \n");
    for (int i = 0; i < n; i++) {
        for (int j = 0; j < m; j++) {
            scanf("%d", &max[i][j]);
        }
    }
    
    printf("Enter allocation matrix: \n");
    for (int i = 0; i < n; i++) {
        for (int j = 0; j < m; j++) {
            scanf("%d", &allocation[i][j]);
        }
    }
    
    calculateNeed();
    
    if (isSafe()) {
        printf("System is in a safe state.\n");
    } else {
        printf("System is in an unsafe state.\n");
    }
    return 0;
}